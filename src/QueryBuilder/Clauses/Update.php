<?php

namespace Saraf\QB\QueryBuilder\Clauses;

use Saraf\QB\QueryBuilder\Capability\Table;
use Saraf\QB\QueryBuilder\Capability\Where;
use Saraf\QB\QueryBuilder\Core\Builder;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\EQuery;
use Saraf\QB\QueryBuilder\Core\Query;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

class Update
{
    use Table;
    use Where;

    public array $updates = [];

    public function __construct(protected DBFactory|null $factory = null)
    {
    }

    public function setUpdate(string $column, mixed $update, bool $escape = true): static
    {
        if ($escape) {
            $this->updates[] = sprintf("%s = %s", $this->keyEscape($column), $this->escape($update));
        } else {
            $this->updates[] = sprintf("%s = %s", $this->keyEscape($column), $update);
        }

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function setUpdates(array $columns, bool $escape = true): static
    {
        foreach ($columns as $column => $update) {
            if (!is_string($column)) {
                throw new QueryBuilderException("Update Required a Key-Value Format");
            }

            $this->setUpdate($column, $update, $escape);
        }

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function compile(): Query|EQuery
    {
        if (!isset($this->updateTable) || empty(trim($this->updateTable))) {
            throw new QueryBuilderException("Table is Required");
        }

        if (count($this->updates) == 0) {
            throw new QueryBuilderException("Updates Required");
        }

        $where = Builder::where($this->whereStatements);
        if (strlen($where) == 0) {
            throw new QueryBuilderException("Where Clause Required");
        }

        $baseQuery = Builder::setUpdateTable($this->updateTable);
        $baseQuery .= Builder::setUpdates($this->updates);
        $baseQuery .= $where;

        if (is_null($this->factory)) {
            return new Query($baseQuery);
        }

        return new EQuery($baseQuery, $this->factory);
    }
}
