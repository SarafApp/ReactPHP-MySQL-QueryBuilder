<?php

namespace Saraf\QB\QueryBuilder\Core;

use React\Mysql\MysqlClient;
use React\Mysql\MysqlResult;
use React\Promise\PromiseInterface;

class DBWorker
{
    protected MysqlClient $connection;
    protected int $jobs;

    public function __construct(MysqlClient $connection)
    {
        $this->connection = $connection;
        $this->jobs = 0;
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

    protected function handleResult(MysqlResult $result): array
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

    protected function startJob(): void
    {
        ++$this->jobs;
    }

    protected function endJob(): void
    {
        --$this->jobs;
    }

    protected function getConnection(): MysqlClient
    {
        return $this->connection;
    }

    public function getJobs(): int
    {
        return $this->jobs;
    }
}
