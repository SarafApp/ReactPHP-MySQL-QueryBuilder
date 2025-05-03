<?php

namespace Saraf\QB\QueryBuilder\Helpers\QueryResult;

class QueryResult
{
    public function __construct(
        public bool  $result,
        public ?int  $count = null,
        public array $rows = [],
        public ?int  $affectedRows = null,
        public ?int  $insertId = null
    )
    {
    }
}