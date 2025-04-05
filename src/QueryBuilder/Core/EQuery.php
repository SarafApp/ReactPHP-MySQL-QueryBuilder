<?php

namespace Saraf\QB\QueryBuilder\Core;

use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Stream\ReadableStreamInterface;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;

final class EQuery
{
    public function __construct(
        protected string    $query,
        protected DBFactory $factory
    )
    {
    }

    public function commit(): PromiseInterface
    {
        try {
            return $this->factory
                ->query($this->query);
        } catch (DBFactoryException $e) {
            return new Promise(function (callable $resolve) use ($e) {
                $resolve([
                    'result' => false,
                    'error' => $e->getMessage()
                ]);
            });
        }
    }

    public function stream(): StreamEventHandler
    {
        return $this->factory->streamQuery($this->query);
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
