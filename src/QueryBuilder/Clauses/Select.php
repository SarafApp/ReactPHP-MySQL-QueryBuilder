<?php

namespace Saraf\QB\QueryBuilder\Clauses;

use Saraf\QB\QueryBuilder\Capability\From;
use Saraf\QB\QueryBuilder\Capability\Group;
use Saraf\QB\QueryBuilder\Capability\Join;
use Saraf\QB\QueryBuilder\Capability\Limit;
use Saraf\QB\QueryBuilder\Capability\Order;
use Saraf\QB\QueryBuilder\Capability\Where;
use Saraf\QB\QueryBuilder\Core\Builder;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\EQuery;
use Saraf\QB\QueryBuilder\Core\Query;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

class Select
{
    use Where;
    use From;
    use Limit;
    use Join;
    use Group;
    use Order;

    protected array $statements = [];
    private bool $isDistinct = false;

    public function __construct(protected DBFactory|null $factory = null)
    {
    }

    /**
     * @throws QueryBuilderException
     */
    public function addColumn(string $column, bool $escape = true): static
    {
        if (isset($this->statements[0]) && $this->statements[0] == "*") {
            throw new QueryBuilderException("All Columns Selected");
        }

        if (empty($column)) {
            throw new QueryBuilderException("Column cant set empty");
        }

        $this->statements[] = $escape
            ? $this->keyEscape($column)
            : $column;

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function addColumns(array $columns, bool $escape = true): static
    {
        foreach ($columns as $column) {
            $this->addColumn($column, $escape);
        }

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function addAllColumns(): static
    {
        if (count($this->statements) > 0) {
            throw new QueryBuilderException("Some Columns Already Set");
        }

        $this->statements[] = "*";

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function addColumnSum(string $column, string|null $alias = null, bool $escapeKey = true, bool $escapeAlias = true): static
    {
        if (empty($column)) {
            throw new QueryBuilderException("Column cant set empty");
        }

        if ($escapeKey) {
            $column = $this->keyEscape($column);
        }

        if (is_null($alias)) {
            return $this->addColumn(sprintf("SUM(%s)", $column), false);
        } else {
            return $this->addColumnAsAlias(sprintf("SUM(%s)", $column), $alias, false, $escapeAlias);
        }
    }

    /**
     * @throws QueryBuilderException
     */
    public function addColumnCount(string $column = "*", string|null $alias = null, bool $escapeKey = true, bool $escapeAlias = true): static
    {
        if ($escapeKey) {
            $column = $this->keyEscape($column);
        }

        if (is_null($alias)) {
            return $this->addColumn(sprintf("COUNT(%s)", $column), false);
        } else {
            return $this->addColumnAsAlias(sprintf("COUNT(%s)", $column), $alias, false, $escapeAlias);
        }
    }

    /**
     * @throws QueryBuilderException
     */
    public function addColumnAverage(string $column, string|null $alias = null, bool $escapeKey = true, bool $escapeAlias = true): static
    {
        if (empty($column)) {
            throw new QueryBuilderException("Column cant set empty");
        }

        if ($escapeKey) {
            $column = $this->keyEscape($column);
        }

        if (is_null($alias)) {
            return $this->addColumn(sprintf("AVG(%s)", $column), false);
        } else {
            return $this->addColumnAsAlias(sprintf("AVG(%s)", $column), $alias, false, $escapeAlias);
        }
    }

    /**
     * @throws QueryBuilderException
     */
    public function addColumnMax(string $column, string|null $alias = null, bool $escapeKey = true, bool $escapeAlias = true): static
    {
        if (empty($column)) {
            throw new QueryBuilderException("Column cant set empty");
        }

        if ($escapeKey) {
            $column = $this->keyEscape($column);
        }

        if (is_null($alias)) {
            return $this->addColumn(sprintf("MAX(%s)", $column), false);
        } else {
            return $this->addColumnAsAlias(sprintf("MAX(%s)", $column), $alias, false, $escapeAlias);
        }
    }

    /**
     * @throws QueryBuilderException
     */
    public function addColumnMin(string $column, string|null $alias = null, bool $escapeKey = true, bool $escapeAlias = true): static
    {
        if (empty($column)) {
            throw new QueryBuilderException("Column cant set empty");
        }

        if ($escapeKey) {
            $column = $this->keyEscape($column);
        }

        if (is_null($alias)) {
            return $this->addColumn(sprintf("MIN(%s)", $column), false);
        } else {
            return $this->addColumnAsAlias(sprintf("MIN(%s)", $column), $alias, false, $escapeAlias);
        }
    }

    /**
     * @throws QueryBuilderException
     */
    public function addColumnAsAlias(string $column, string $alias, bool $escapeKey = true, bool $escapeAlias = true): static
    {
        if (empty($column)) {
            throw new QueryBuilderException("Column cant set empty");
        }

        $this->addColumn(sprintf(
            "%s AS %s",
            $escapeKey ? $this->keyEscape($column) : $column,
            $escapeAlias ? $this->keyEscape($alias) : $alias
        ), false);

        return $this;
    }

    public function setDistinct(bool $enable = true): static
    {
        $this->isDistinct = $enable;
        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function compile(): Query|EQuery
    {
        if (!isset($this->fromTable) || empty(trim($this->fromTable))) {
            throw new QueryBuilderException("From is Required");
        }

        if (count($this->statements) == 0) {
            $this->addAllColumns();
        }

        $baseQuery = Builder::select($this->statements, $this->isDistinct);

        $baseQuery .= Builder::from($this->fromTable);
        $baseQuery .= Builder::joins($this->joins);
        $baseQuery .= Builder::where($this->whereStatements);
        $baseQuery .= Builder::groupBy($this->groupBy);
        $baseQuery .= Builder::orderBy($this->orderBy);

        if (isset($this->count)) {
            $baseQuery .= Builder::count($this->count);
            if (isset($this->offset)) {
                $baseQuery .= Builder::offset($this->offset);
            }
        }
        if (is_null($this->factory)) {
            return new Query($baseQuery);
        }

        return new EQuery($baseQuery, $this->factory);
    }
}
