<?php

namespace Saraf\QB\QueryBuilder\Core;

use React\EventLoop\LoopInterface;
use React\MySQL\Factory;
use React\Promise\PromiseInterface;
use React\Stream\ReadableStreamInterface;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;
use Saraf\QB\QueryBuilder\Exceptions\TransactionException;
use Saraf\QB\QueryBuilder\Helpers\QBHelper;
use Saraf\QB\QueryBuilder\QueryBuilder;
use function React\Promise\all;
use function React\Promise\resolve;

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
        protected string         $host,
        protected string         $dbName,
        protected string         $username,
        protected string         $password,
        protected int            $writePort = 6446,
        protected int            $readPort = 6447,
        protected int            $writeInstanceCount = 1,
        protected int            $readInstanceCount = 1,
        protected int            $timeout = 30,
        protected int            $idle = 60,
        protected string         $charset = 'utf8mb4',
        protected bool           $debugMode = false,
        protected int            $maxWriteConnections = 1_000_000_000
    )
    {
        $this->factory = new Factory($loop);
        $this->createConnections();
    }

    public function getTrace(): array
    {
        if ($this->debugMode === false)
            return [
                'result' => false,
                'error' => 'NOT_IN_DEBUG_MODE'
            ];

        $jobs = [];
        foreach ($this->writeConnections as $i => $writeConnection) {
            @$jobs['write'][$i] = $writeConnection->getJobs();
        }

        foreach ($this->readConnections as $s => $readConnection) {
            @$jobs['read'][$s] = $readConnection->getJobs();
        }

        return [
            'result' => true,
            'logs' => $this->logs,
            'workers' => $jobs
        ];
    }

    /**
     * @throws DBFactoryException
     */
    protected function createConnections(): static
    {
        if (count($this->readConnections) > 0 || count($this->writeConnections) > 0)
            throw new DBFactoryException("Connections Already Created");

        for ($i = 0; $i < $this->writeInstanceCount; ++$i) {
            $this->writeConnections[] = $this->createWriteConnection();
        }

        for ($s = 0; $s < $this->readInstanceCount; ++$s) {
            $this->readConnections[] = new DBWorker(
                $this->factory
                    ->createLazyConnection(
                        sprintf(
                            "%s:%s@%s:%s/%s?idle=%s&timeout=%s",
                            $this->username,
                            urlencode($this->password),
                            $this->host,
                            $this->readPort,
                            $this->dbName,
                            $this->idle,
                            $this->timeout
                        )
                    )
            );
        }
        return $this;
    }

    /**
     * @throws DBFactoryException
     */
    public function getQueryBuilder(): QueryBuilder
    {
        if (count($this->readConnections) == 0 || count($this->writeConnections) == 0)
            throw new DBFactoryException("Connections Not Created");

        return new QueryBuilder($this);
    }

    /**
     * @throws DBFactoryException
     */
    public function query(string $query): PromiseInterface
    {
        $isWrite = true;
        if (str_starts_with(strtolower(trim($query, " \n\r\t\v\0(")), "select")
            || str_starts_with(strtolower(trim($query, " \n\r\t\v\0(")), "show")
        ) $isWrite = false;

        $bestConnections = $this->getBestConnection();

        if ($isWrite && $bestConnections['write'] === null)
            throw new DBFactoryException("All write connections reserved by transactions");

        $connection = $isWrite
            ? $this->writeConnections[$bestConnections['write']]
            : $this->readConnections[$bestConnections['read']];

        if (!($connection instanceof DBWorker))
            throw new DBFactoryException("Connections Not Instance of Worker / Restart App");

        if (!$this->debugMode)
            return $connection->query($query);

        $startTime = QBHelper::getCurrentMicroTime();
        return $connection->query($query)
            ->then(function ($result) use ($isWrite, $startTime, $query) {
                $endTime = QBHelper::getCurrentMicroTime();
                $this->logs[] = [
                    'query' => $query,
                    'took' => $endTime - $startTime,
                    'isWrite' => $isWrite,
                    'status' => $result['result']
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
        if (str_starts_with(strtolower(trim($query, " \n\r\t\v\0(")), "select")
            || str_starts_with(strtolower(trim($query, " \n\r\t\v\0(")), "show")
        ) $isWrite = false;

        $bestConnections = $this->getBestConnection();

        $connection = $isWrite
            ? $this->writeConnections[$bestConnections['write']]
            : $this->readConnections[$bestConnections['read']];

        if (!($connection instanceof DBWorker))
            throw new DBFactoryException("Connections Not Instance of Worker / Restart App");

        return $connection->streamQuery($query);
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\DBFactoryException
     */
    public function streamQueryRaw(string $query): ReadableStreamInterface
    {
        $isWrite = true;
        if (str_starts_with(strtolower(trim($query, " \n\r\t\v\0(")), "select")
            || str_starts_with(strtolower(trim($query, " \n\r\t\v\0(")), "show")
        ) $isWrite = false;

        $bestConnections = $this->getBestConnection();

        $connection = $isWrite
            ? $this->writeConnections[$bestConnections['write']]
            : $this->readConnections[$bestConnections['read']];

        if (!($connection instanceof DBWorker))
            throw new DBFactoryException("Connections Not Instance of Worker / Restart App");

        return $connection->streamQueryRaw($query);
    }

    /**
     * @throws DBFactoryException
     */
    private function getBestConnection(): array
    {
        if (count($this->readConnections) == 0 && count($this->writeConnections) == 0)
            throw new DBFactoryException("Connections Not Created");

        // Best Writer
        $minWriteJobs = self::MAX_CONNECTION_COUNT;
        $minJobsWriterConnection = null;

        foreach ($this->writeConnections as $i => $writeConnection) {
            if ($writeConnection->isReserved()) continue;
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
            'read' => $minJobsReaderConnection
        ];
    }

    protected function createWriteConnection(): DBWorker
    {
        return new DBWorker(
            $this->factory
                ->createLazyConnection(
                    sprintf(
                        "%s:%s@%s:%s/%s?idle=%s&timeout=%s&charset=%s",
                        $this->username,
                        urlencode($this->password),
                        $this->host,
                        $this->writePort,
                        $this->dbName,
                        $this->idle,
                        $this->timeout,
                        $this->charset
                    )
                )
        );
    }

    /**
     * @throws TransactionException
     */
    public function reserveWriteConnection(): DBWorker
    {
        $candidate = null;
        $minJobs = self::MAX_CONNECTION_COUNT;
        foreach ($this->writeConnections as $writeConnection) {
            if ($writeConnection->isReserved()) continue;
            if ($writeConnection->getJobs() < $minJobs) {
                $minJobs = $writeConnection->getJobs();
                $candidate = $writeConnection;
            }
        }

        if ($candidate === null) {
            if (count($this->writeConnections) >= $this->maxWriteConnections) {
                throw new TransactionException(
                    "All write connections reserved (cap=" . $this->maxWriteConnections . ")"
                );
            }
            $candidate = $this->createWriteConnection();
            $this->writeConnections[] = $candidate;
        }

        $candidate->reserve();
        return $candidate;
    }

    public function transaction(callable $work): PromiseInterface
    {
        try {
            $worker = $this->reserveWriteConnection();
        } catch (TransactionException $e) {
            return resolve([
                'result' => false,
                'error' => $e->getMessage(),
                'failedAt' => 'reserve',
            ]);
        }

        return (new Transaction($this, $worker))->run($work);
    }

    public function warmUp(): PromiseInterface
    {
        $warmUpQuery = "SELECT @@hostname";
        $list = [];
        foreach ($this->writeConnections as $connection) {
            $list[] = $connection->query($warmUpQuery);
        }

        foreach ($this->readConnections as $connection) {
            $list[] = $connection->query($warmUpQuery);
        }

        if (count($list) == 0)
            return resolve([
                'result' => false,
                'error' => "WarmUp failed",
                'trace' => "No Active Instances to WarmUp"
            ]);

        return all($list)
            ->then(function ($results) {
                foreach ($results as $result) {
                    if (!$result['result'])
                        return [
                            'result' => false,
                            'error' => "WarmUp failed",
                            'trace' => $result['error']
                        ];
                }

                return [
                    'result' => true,
                    'status' => "WarmUp Success",
                ];
            });
    }


}
