<?php

namespace Saraf\QB\QueryBuilder\Clauses;

use Saraf\QB\QueryBuilder\Capability\Into;
use Saraf\QB\QueryBuilder\Core\Builder;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\EQuery;
use Saraf\QB\QueryBuilder\Core\Query;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

class InsertUpdate
{
    use Into;

    protected array $columns = [];
    protected array $row = [];
    protected array $updates = [];

    public function __construct(protected DBFactory|null $factory = null)
    {
    }

    /**
     * @throws QueryBuilderException
     */
    public function setColumns(array $columns, bool $escapeKey = true): static
    {
        if (count($this->columns) > 0) {
            throw new QueryBuilderException("Columns already set");
        }

        if (count($this->row) != 0) {
            throw new QueryBuilderException("Instance Have Some rows so column cant change");
        }

        if ($escapeKey) {
            foreach ($columns as $columnName => $columnValue) {
                $columns[$columnName] = $this->keyEscape($columnValue);
            }
        }

        $this->columns = $columns;

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function setRow(array $row, bool $escapeValue = true): static
    {
        if (count($this->columns) == 0) {
            throw new QueryBuilderException("Columns not set");
        }

        if (count($this->row) > 0) {
            throw new QueryBuilderException("Row Already Set");
        }

        if (count($row) != count($this->columns)) {
            throw new QueryBuilderException("Columns and Rows Must Have same counts");
        }

        foreach ($row as $itemKey => $itemVal) {
            if ($escapeValue || is_bool($itemVal)) {
                $row[$itemKey] = $this->escape($itemVal);
            }
        }

        $this->row = $row;

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function setUpdate(string $column, mixed $value, bool $escapeValue = true, bool $escapeKey = true): static
    {
        if (count($this->columns) == 0) {
            throw new QueryBuilderException("Columns not set");
        }

        if (count($this->row) == 0) {
            throw new QueryBuilderException("Row not set");
        }


        if ($escapeKey) {
            $column = $this->keyEscape($column);
        }

        if ($escapeValue || is_bool($value)) {
            $this->updates[$column] = $this->escape($value);
        } else {
            $this->updates[$column] = $value;
        }

        return $this;
    }


    /**
     * @throws QueryBuilderException
     */
    public function setUpdates(array $updates, bool $escapeValues = true, bool $escapeKeys = true): static
    {
        foreach ($updates as $updateKey => $updateValue) {
            $this->setUpdate($updateKey, $updateValue, $escapeValues, $escapeKeys);
        }

        return $this;
    }


    /**
     * @throws QueryBuilderException
     */
    public function compile(): Query|EQuery
    {
        if (!isset($this->intoTable) || empty(trim($this->intoTable))) {
            throw new QueryBuilderException("Table Required");
        }

        if (count($this->columns) == 0) {
            throw new QueryBuilderException("Columns Required");
        }

        if (count($this->row) == 0) {
            throw new QueryBuilderException("Rows Required");
        }

        if (count($this->updates) == 0) {
            throw new QueryBuilderException("Updates Required");
        }

        $baseQuery = Builder::setInsertTable($this->intoTable);
        $baseQuery .= Builder::setInsertColumns($this->columns);
        $baseQuery .= Builder::setInsertRows([$this->row]);
        $baseQuery .= Builder::setOnDuplicateKeyUpdate($this->updates);

        if (is_null($this->factory)) {
            return new Query($baseQuery);
        }

        return new EQuery($baseQuery, $this->factory);
    }
}
