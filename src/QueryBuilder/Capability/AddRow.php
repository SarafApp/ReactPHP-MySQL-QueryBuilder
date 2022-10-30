<?php

namespace Saraf\QB\QueryBuilder\Capability;

use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;
use Saraf\QB\QueryBuilder\Helpers\Escape;

trait AddRow
{
    use Escape;

    protected array $columns = [];

    /**
     * @throws QueryBuilderException
     */
    public function addRow(array $row, bool $escapeValue = true): static
    {
        if (count($this->columns) == 0)
            throw new QueryBuilderException("Columns not set");

        if (count($row) != count($this->columns))
            throw new QueryBuilderException("Columns and Rows Must Have same counts");

        foreach ($row as $itemKey => $itemVal) {
            if ($escapeValue || is_bool($itemVal))
                $row[$itemKey] = $this->escape($itemVal);
        }

        $this->rows[] = $row;

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function addRows(array $rows, bool $escapeValue = true): static
    {
        if (count($rows) == count($rows, COUNT_RECURSIVE))
            throw new QueryBuilderException("Array must be MultiDimensional");

        foreach ($rows as $row) {
            $this->addRow($row, $escapeValue);
        }

        return $this;
    }

}
