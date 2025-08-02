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

    public function toArray(): array
    {
        return [
            'result' => $this->result,
            'count' => $this->count,
            'rows' => $this->rows,
            'affectedRows' => $this->affectedRows,
            'insertId' => $this->insertId
        ];
    }
}