<?php

namespace Saraf\QB\QueryBuilder\Contracts;

use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResult;

interface QueryResultCollectionContract
{
    public function add(string $name, QueryResult $queryResult): self;
    public function get(string $name): QueryResult;
}