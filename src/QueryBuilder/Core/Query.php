<?php

namespace Saraf\QB\QueryBuilder\Core;

final class Query
{
    public function __construct(protected string $query)
    {
    }

    public function getQueryAsString(): string
    {
        return $this->query;
    }

    public function getQuery(): string
    {
        return $this->getQueryAsString();
    }
}
