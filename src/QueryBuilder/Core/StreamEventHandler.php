<?php

namespace Saraf\QB\QueryBuilder\Core;

use React\MySQL\ConnectionInterface;

class StreamEventHandler
{
    protected $onError = null;
    protected $onData = null;
    protected $onClosed = null;

    public function __construct(
        protected ConnectionInterface $connection,
        protected string              $query,
        protected ?\Closure           $onClosedWorker = null
    )
    {
    }

    public function onError(callable $onError): StreamEventHandler
    {
        $this->onError = $onError;
        return $this;
    }

    public function onData(callable $onData): StreamEventHandler
    {
        $this->onData = $onData;
        return $this;
    }

    public function onClosed(callable $onClosed): StreamEventHandler
    {
        $this->onClosed = $onClosed;
        return $this;
    }

    public function run(): void
    {
        $stream = $this->connection->queryStream($this->query);

        if ($this->onError != null)
            $stream->on("error", $this->onError);
        if ($this->onData != null)
            $stream->on("data", $this->onData);
        if ($this->onClosed != null)
            $stream->on("close", $this->onClosed);

        // For Handling Inner Queue
        if ($this->onClosedWorker != null)
            $stream->on("close", $this->onClosedWorker);
    }

}