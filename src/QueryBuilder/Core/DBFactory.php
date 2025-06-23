<?php

namespace Saraf\QB\QueryBuilder\Core;

use React\EventLoop\LoopInterface;
use React\MySQL\Factory;
use React\Mysql\MysqlClient;
use React\Promise\PromiseInterface;
use React\Stream\ReadableStreamInterface;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;
use Saraf\QB\QueryBuilder\Helpers\QBHelper;
use Saraf\QB\QueryBuilder\QueryBuilder;

class DBFactory
{
    private array $logs = [];
    private const MAX_CONNECTION_COUNT = 1000000000;

    protected Factory $factory;
    protected array $writeConnections = [];
    protected array $readConnections = [];

    /**
     * @throws DBFactoryException
     */
    public function __construct(
        protected ?LoopInterface $loop,
        protected string $host,
        protected string $dbName,
        protected string $username,
        protected string $password,
        protected int $writePort = 6446,
        protected int $readPort = 6447,
        protected int $writeInstanceCount = 2,
        protected int $readInstanceCount = 2,
        protected int $timeout = 2,
        protected int $idle = 2,
        protected string $charset = 'utf8mb4',
        protected bool $debugMode = false,
    ) {
        $this->factory = new Factory($loop);
        $this->createConnections();
    }

    public function getTrace(): array
    {
        if ($this->debugMode === false) {
            return [
                'result' => false,
                'error'  => 'NOT_IN_DEBUG_MODE',
            ];
        }

        $jobs = [];
        foreach ($this->writeConnections as $i => $writeConnection) {
            @$jobs['write'][$i] = $writeConnection->getJobs();
        }

        foreach ($this->readConnections as $s => $readConnection) {
            @$jobs['read'][$s] = $readConnection->getJobs();
        }

        return [
            'result'  => true,
            'logs'    => $this->logs,
            'workers' => $jobs,
        ];
    }

    /**
     * @throws DBFactoryException
     */
    protected function createConnections(): static
    {
        if (count($this->readConnections) > 0 || count($this->writeConnections) > 0) {
            throw new DBFactoryException("Connections Already Created");
        }

        for ($i = 0; $i < $this->writeInstanceCount; ++$i) {
            $this->writeConnections[] = new DBWorker(
                new MysqlClient(
                    sprintf(
                        "%s:%s@%s:%s/%s?idle=%s&timeout=%s&charset=%s",
                        $this->username,
                        urlencode($this->password),
                        $this->host,
                        $this->writePort,
                        $this->dbName,
                        $this->idle,
                        $this->timeout,
                        $this->charset,
                    ),
                ),
            );
        }

        for ($s = 0; $s < $this->readInstanceCount; ++$s) {
            $this->readConnections[] = new DBWorker(
                new MysqlClient(
                    sprintf(
                        "%s:%s@%s:%s/%s?idle=%s&timeout=%s",
                        $this->username,
                        urlencode($this->password),
                        $this->host,
                        $this->readPort,
                        $this->dbName,
                        $this->idle,
                        $this->timeout,
                    ),
                ),
            );
        }

        return $this;
    }

    /**
     * @throws DBFactoryException
     */
    public function getQueryBuilder(): QueryBuilder
    {
        if (count($this->readConnections) == 0 || count($this->writeConnections) == 0) {
            throw new DBFactoryException("Connections Not Created");
        }

        return new QueryBuilder($this);
    }

    /**
     * @throws DBFactoryException
     */
    public function query(string $query): PromiseInterface
    {
        $isWrite = true;
        if (str_starts_with(strtolower($query), "select")
            || str_starts_with(strtolower($query), "show")
        ) {
            $isWrite = false;
        }

        $bestConnections = $this->getBestConnection();

        $connection = $isWrite
            ? $this->writeConnections[$bestConnections['write']]
            : $this->readConnections[$bestConnections['read']];

        if (!($connection instanceof DBWorker)) {
            throw new DBFactoryException("Connections Not Instance of Worker / Restart App");
        }

        if (!$this->debugMode) {
            return $connection->query($query);
        }

        $startTime = QBHelper::getCurrentMicroTime();

        return $connection
            ->query($query)
            ->then(function ($result) use ($isWrite, $startTime, $query) {
                $endTime = QBHelper::getCurrentMicroTime();
                $this->logs[] = [
                    'query'   => $query,
                    'took'    => $endTime - $startTime,
                    'isWrite' => $isWrite,
                    'status'  => $result['result'],
                ];

                return $result;
            });
    }

    /**
     * @throws DBFactoryException
     */
    public function streamQuery(string $query): StreamEventHandler
    {
        $isWrite = true;
        if (str_starts_with(strtolower($query), "select")
            || str_starts_with(strtolower($query), "show")
        ) {
            $isWrite = false;
        }

        $bestConnections = $this->getBestConnection();

        $connection = $isWrite
            ? $this->writeConnections[$bestConnections['write']]
            : $this->readConnections[$bestConnections['read']];

        if (!($connection instanceof DBWorker)) {
            throw new DBFactoryException("Connections Not Instance of Worker / Restart App");
        }

        return $connection->streamQuery($query);
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\DBFactoryException
     */
    public function streamQueryRaw(string $query): ReadableStreamInterface
    {
        $isWrite = true;
        if (str_starts_with(strtolower($query), "select")
            || str_starts_with(strtolower($query), "show")
        ) {
            $isWrite = false;
        }

        $bestConnections = $this->getBestConnection();

        $connection = $isWrite
            ? $this->writeConnections[$bestConnections['write']]
            : $this->readConnections[$bestConnections['read']];

        if (!($connection instanceof DBWorker)) {
            throw new DBFactoryException("Connections Not Instance of Worker / Restart App");
        }

        return $connection->streamQueryRaw($query);
    }

    /**
     * @throws DBFactoryException
     */
    private function getBestConnection(): array
    {
        if (count($this->readConnections) == 0 || count($this->writeConnections) == 0) {
            throw new DBFactoryException("Connections Not Created");
        }

        // Best Writer
        $minWriteJobs = self::MAX_CONNECTION_COUNT;
        $minJobsWriterConnection = null;

        foreach ($this->writeConnections as $i => $writeConnection) {
            if ($writeConnection->getJobs() < $minWriteJobs) {
                $minWriteJobs = $writeConnection->getJobs();
                $minJobsWriterConnection = $i;
            }
        }

        // Best Read
        $minReadJobs = self::MAX_CONNECTION_COUNT;
        $minJobsReaderConnection = null;

        foreach ($this->readConnections as $s => $readConnection) {
            if ($readConnection->getJobs() < $minReadJobs) {
                $minReadJobs = $readConnection->getJobs();
                $minJobsReaderConnection = $s;
            }
        }

        return [
            'write' => $minJobsWriterConnection,
            'read'  => $minJobsReaderConnection,
        ];
    }


}
