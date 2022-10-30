<?php

namespace Saraf\QB\QueryBuilder\Clauses;

use Saraf\QB\QueryBuilder\Capability\AddRow;
use Saraf\QB\QueryBuilder\Capability\Into;
use Saraf\QB\QueryBuilder\Core\Builder;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\EQuery;
use Saraf\QB\QueryBuilder\Core\Query;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

class MultiInsertUpdate
{
    use Into;
    use AddRow;

    protected string $alias;
    protected array $rows = [];
    protected array $updates = [];

    public function __construct(protected DBFactory|null $factory = null)
    {
    }

    /**
     * @throws QueryBuilderException
     */
    public function setColumns(array $columns, bool $escapeKey = true): static
    {
        if (count($this->rows) != 0)
            throw new QueryBuilderException("Instance Have Some rows so column cant change");

        if ($escapeKey) {
            foreach ($columns as $columnName => $columnValue) {
                $columns[$columnName] = $this->keyEscape($columnValue);
            }
        }

        $this->columns = $columns;

        return $this;
    }

    public function setInsertAlias(string $aliasName, bool $escape = true): static
    {
        if ($escape)
            $this->alias = $this->keyEscape($aliasName);
        else
            $this->alias = $aliasName;

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function addUpdate(string $key, mixed $value, bool $escapeValue = true, bool $escapeKey = true): static
    {
        if (count($this->columns) == 0)
            throw new QueryBuilderException("Columns not set");

        if (count($this->rows) == 0)
            throw new QueryBuilderException("Rows not set");

        if ($escapeKey)
            $key = $this->keyEscape($key);

        if ($escapeValue || is_bool($value) || is_null($value))
            $value = $this->escape($value);

        $this->updates[$key] = $value;

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function addUpdates(array $updates, bool $escapeValue = true, bool $escapeKey = true): static
    {
        foreach ($updates as $updateKey => $updateValue) {
            $this->addUpdate($updateKey, $updateValue, $escapeValue, $escapeKey);
        }

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function compile(): Query|EQuery
    {
        if (!isset($this->intoTable) || empty(trim($this->intoTable)))
            throw new QueryBuilderException("Table Required");

        if (!isset($this->alias) || empty(trim($this->alias)))
            throw new QueryBuilderException("Alias Required");

        if (count($this->columns) == 0)
            throw new QueryBuilderException("Columns Required");

        if (count($this->rows) == 0)
            throw new QueryBuilderException("Rows Required");

        if (count($this->updates) == 0)
            throw new QueryBuilderException("Updates Required");

        $baseQuery = Builder::setInsertTable($this->intoTable);
        $baseQuery .= Builder::setInsertColumns($this->columns);
        $baseQuery .= Builder::setInsertRows($this->rows);
        $baseQuery .= Builder::asAlias($this->alias);
        $baseQuery .= Builder::setOnDuplicateKeyUpdate($this->updates);

        if (is_null($this->factory))
            return new Query($baseQuery);

        return new EQuery($baseQuery, $this->factory);
    }
}
