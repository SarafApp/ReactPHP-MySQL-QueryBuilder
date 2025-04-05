<?php

namespace Saraf\QB\QueryBuilder\Core;

use React\MySQL\ConnectionInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class StreamEventHandler
{
    protected \Closure|null $onDataFn = null;
    protected Deferred $promise;

    public function __construct(
        protected ConnectionInterface $connection,
        protected string              $query,
        protected ?\Closure           $onClosedWorker = null
    )
    {
    }

    public function onData(\Closure $onDataFn): StreamEventHandler
    {
        $this->onDataFn = $onDataFn;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function run(mixed $initialValue = null): PromiseInterface
    {
        $promise = new Deferred();

        if ($this->onDataFn == null) {
            throw new \Exception("onData is required");
        }


        $stream = $this->connection->queryStream($this->query);

        $stream->on("data", function ($row) use (&$initialValue) {
            $initialValue = ($this->onDataFn)($row, $initialValue);
        });

        $stream->on("error", function (\Throwable $error) use ($promise) {
            $promise->resolve([
                'result' => false,
                'error' => $error->getMessage(),
            ]);
        });
        $stream->on("close", function () use (&$initialValue, $promise) {
            $promise->resolve([
                'result' => true,
                'data' => $initialValue
            ]);
        });

        // For Handling Inner Queue
        if ($this->onClosedWorker != null)
            $stream->on("close", $this->onClosedWorker);

        return $promise->promise();
    }

}