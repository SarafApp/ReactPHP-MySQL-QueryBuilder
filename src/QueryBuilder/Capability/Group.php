<?php

namespace Saraf\QB\QueryBuilder\Capability;

use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;
use Saraf\QB\QueryBuilder\Helpers\Escape;

trait Group
{
    use Escape;

    private array $groupBy = [];

    /**
     * @throws QueryBuilderException
     */
    public function groupBy(string|array $groupColumns, bool $escape = true): self
    {
        if (is_string($groupColumns)) {
            if (empty(trim($groupColumns)))
                throw new QueryBuilderException("GroupBy cant set as empty string");

            if ($escape) {
                $groupColumns = $this->keyEscape($groupColumns);
            }
            $this->groupBy[] = $groupColumns;
        } else {
            if (count($groupColumns) == 0)
                throw new QueryBuilderException("GroupBy cant set empty");

            foreach ($groupColumns as $columnKey => $columnValue) {
                if (empty(trim($columnValue)))
                    throw new QueryBuilderException("GroupBy cant set as empty string");

                if ($escape)
                    $groupColumns[$columnKey] = $this->keyEscape($columnValue);
            }
            array_push($this->groupBy, ...$groupColumns);
        }

        return $this;
    }
}
