<?php

namespace Saraf\QB\QueryBuilder\Core;

use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Stream\ReadableStreamInterface;
use Saraf\QB\QueryBuilder\Exceptions\TransactionException;

final class PinnedEQuery
{
    public function __construct(
        protected string   $query,
        protected DBWorker $reservedWorker
    )
    {
    }

    public function commit(): PromiseInterface
    {
        return $this->reservedWorker->query($this->query);
    }

    public function stream(): StreamEventHandler
    {
        return $this->reservedWorker->streamQuery($this->query);
    }

    /**
     * @throws TransactionException
     */
    public function streamRaw(): ReadableStreamInterface
    {
        throw new TransactionException("Raw streams inside transactions are not supported");
    }

    public function getQuery(): Promise
    {
        return new Promise(function (callable $resolve) {
            $resolve([
                'result' => true,
                'query' => $this->query
            ]);
        });
    }
}
