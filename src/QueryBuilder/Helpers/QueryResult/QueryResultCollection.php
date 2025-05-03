<?php

namespace Saraf\QB\QueryBuilder\Helpers\QueryResult;

use IteratorAggregate;
use Saraf\QB\QueryBuilder\Contracts\QueryResultCollectionContract;

class QueryResultCollection implements IteratorAggregate, QueryResultCollectionContract
{
    protected array $queryResults = [];

    public function add(string $name, QueryResult $queryResult): self
    {
        $this->queryResults[$name] = $queryResult;
        return $this;
    }

    public function get(string $name): QueryResult
    {
        return $this->queryResults[$name];
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->queryResults);
    }
}