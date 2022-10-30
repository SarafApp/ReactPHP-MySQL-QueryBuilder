<?php

namespace Saraf\QB\QueryBuilder\Clauses;

use Saraf\QB\QueryBuilder\Capability\AddRow;
use Saraf\QB\QueryBuilder\Capability\Into;
use Saraf\QB\QueryBuilder\Core\Builder;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\EQuery;
use Saraf\QB\QueryBuilder\Core\Query;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

class Insert
{
    use Into;
    use AddRow;

    protected array $rows = [];

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


        foreach ($columns as $columnValue) {
            $this->columns[] = $escapeKey ? $this->keyEscape($columnValue) : $columnValue;
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

        if (count($this->columns) == 0)
            throw new QueryBuilderException("Columns Required");

        if (count($this->rows) == 0)
            throw new QueryBuilderException("Rows Required");

        $baseQuery = Builder::setInsertTable($this->intoTable);
        $baseQuery .= Builder::setInsertColumns($this->columns);
        $baseQuery .= Builder::setInsertRows($this->rows);

        if (is_null($this->factory))
            return new Query($baseQuery);

        return new EQuery($baseQuery, $this->factory);
    }

}
