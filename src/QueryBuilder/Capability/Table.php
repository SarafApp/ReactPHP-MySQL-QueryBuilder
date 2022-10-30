<?php

namespace Saraf\QB\QueryBuilder\Capability;

use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;
use Saraf\QB\QueryBuilder\Helpers\Escape;

trait Table
{
    use Escape;

    private string $updateTable;

    /**
     * @throws QueryBuilderException
     */
    public function table(string $table): static
    {
        if (empty(trim($table)))
            throw new QueryBuilderException("Table is Required");

        $this->updateTable = $this->keyEscape($table);

        return $this;
    }
}
