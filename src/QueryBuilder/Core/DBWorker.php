<?php

namespace Saraf\QB\QueryBuilder\Core;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Stream\ReadableStreamInterface;
use Saraf\QB\QueryBuilder\Exceptions\TransactionException;

class DBWorker
{
    protected ConnectionInterface $connection;
    protected int $jobs;
    protected bool $reserved = false;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->jobs = 0;
    }

    /**
     * @throws TransactionException
     */
    public function reserve(): void
    {
        if ($this->reserved) {
            throw new TransactionException("Worker already reserved");
        }
        $this->reserved = true;
    }

    public function release(): void
    {
        $this->reserved = false;
    }

    public function isReserved(): bool
    {
        return $this->reserved;
    }

    public function query(string $query): PromiseInterface
    {
        $this->startJob();
        return $this->getConnection()
            ->query($query)
            ->then(function ($result) {
                $this->endJob();
                return $this->handleResult($result);
            }, function (\Exception $exception) {
                $this->endJob();
                return $this->handleException($exception);
            });
    }

    public function streamQuery(string $query): StreamEventHandler
    {
        $this->startJob();
        return (new StreamEventHandler($this->getConnection(), $query, function () {
            $this->endJob();
        }));
    }

    public function streamQueryRaw(string $query): ReadableStreamInterface
    {
        return $this->connection->queryStream($query);
    }

    protected function handleResult(QueryResult $result): array
    {
        if (!is_null($result->resultRows)) {
            return [
                'result' => true,
                'count' => count($result->resultRows),
                'rows' => $result->resultRows
            ];
        }

        $res = [
            'result' => true,
            'affectedRows' => $result->affectedRows
        ];

        if ($result->insertId !== 0) {
            $res['insertId'] = $result->insertId;
        }

        return $res;
    }

    protected function handleException(\Exception $exception): array
    {
        return [
            'result' => false,
            'error' => $exception->getMessage(),
        ];
    }

    protected function startJob()
    {
        ++$this->jobs;
    }

    protected function endJob()
    {
        --$this->jobs;
    }

    protected function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function getJobs(): int
    {
        return $this->jobs;
    }
}
