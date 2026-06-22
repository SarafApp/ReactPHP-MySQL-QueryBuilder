<?php

namespace Saraf\QB\QueryBuilder\Core;

use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Saraf\QB\QueryBuilder\Clauses\Delete;
use Saraf\QB\QueryBuilder\Clauses\Insert;
use Saraf\QB\QueryBuilder\Clauses\InsertUpdate;
use Saraf\QB\QueryBuilder\Clauses\MultiInsertUpdate;
use Saraf\QB\QueryBuilder\Clauses\Select;
use Saraf\QB\QueryBuilder\Clauses\Update;
use function React\Promise\resolve;

final class Transaction
{
    public function __construct(
        protected DBFactory $factory,
        protected DBWorker  $reservedWorker
    )
    {
    }

    public function select(): TxBuilder
    {
        return new TxBuilder(new Select(null), $this->reservedWorker);
    }

    public function insert(): TxBuilder
    {
        return new TxBuilder(new Insert(null), $this->reservedWorker);
    }

    public function update(): TxBuilder
    {
        return new TxBuilder(new Update(null), $this->reservedWorker);
    }

    public function delete(): TxBuilder
    {
        return new TxBuilder(new Delete(null), $this->reservedWorker);
    }

    public function insertUpdate(): TxBuilder
    {
        return new TxBuilder(new InsertUpdate(null), $this->reservedWorker);
    }

    public function multiInsertUpdate(): TxBuilder
    {
        return new TxBuilder(new MultiInsertUpdate(null), $this->reservedWorker);
    }

    public function raw(string $sql): PromiseInterface
    {
        return $this->reservedWorker->query($sql);
    }

    public function run(callable $work): PromiseInterface
    {
        $worker = $this->reservedWorker;
        $deferred = new Deferred();

        $finalize = function (array $result) use ($worker, $deferred) {
            $worker->release();
            $deferred->resolve($result);
        };

        $rollbackThen = function (array $failure) use ($worker, $finalize) {
            $worker->query("ROLLBACK")->then(
                function () use ($failure, $finalize) {
                    $finalize($failure);
                },
                function () use ($failure, $finalize) {
                    $finalize($failure);
                }
            );
        };

        $worker->query("START TRANSACTION")->then(function ($beginResult) use ($work, $worker, $finalize, $rollbackThen) {
            if (!($beginResult['result'] ?? false)) {
                $finalize([
                    'result' => false,
                    'error' => $beginResult['error'] ?? 'BEGIN failed',
                    'failedAt' => 'begin',
                ]);
                return;
            }

            try {
                $value = $work($this);
            } catch (\Throwable $e) {
                $rollbackThen([
                    'result' => false,
                    'error' => $e->getMessage(),
                    'failedAt' => 'closure',
                ]);
                return;
            }

            resolve($value)->then(
                function ($v) use ($worker, $finalize, $rollbackThen) {
                    if (is_array($v) && array_key_exists('result', $v) && $v['result'] === false) {
                        $rollbackThen([
                            'result' => false,
                            'error' => $v['error'] ?? 'inner failure',
                            'failedAt' => 'inner',
                        ]);
                        return;
                    }

                    $worker->query("COMMIT")->then(function ($commitResult) use ($v, $finalize, $rollbackThen) {
                        if (!($commitResult['result'] ?? false)) {
                            $rollbackThen([
                                'result' => false,
                                'error' => $commitResult['error'] ?? 'COMMIT failed',
                                'failedAt' => 'commit',
                            ]);
                            return;
                        }
                        $finalize([
                            'result' => true,
                            'data' => $v,
                        ]);
                    });
                },
                function (\Throwable $e) use ($rollbackThen) {
                    $rollbackThen([
                        'result' => false,
                        'error' => $e->getMessage(),
                        'failedAt' => 'inner',
                    ]);
                }
            );
        });

        return $deferred->promise();
    }
}
