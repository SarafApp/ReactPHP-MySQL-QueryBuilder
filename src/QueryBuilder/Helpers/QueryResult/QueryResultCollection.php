<?php

namespace Saraf\QB\QueryBuilder\Helpers\QueryResult;

use IteratorAggregate;
use Saraf\QB\QueryBuilder\Contracts\QueryResultCollectionContract;
use Saraf\QB\QueryBuilder\Exceptions\QueryResultException;

class QueryResultCollection implements IteratorAggregate, QueryResultCollectionContract
{
    protected array $queryResults = [];

    public function add(string $name, QueryResult $queryResult): self
    {
        $this->queryResults[$name] = $queryResult;
        return $this;
    }

    /**
     * @throws QueryResultException
     */
    public function get(string $name): QueryResult
    {
        if (!isset($this->queryResults[$name])) {
            throw new QueryResultException("Query result for '{$name}' does not exist.");
        }
        return $this->queryResults[$name];
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->queryResults);
    }
}