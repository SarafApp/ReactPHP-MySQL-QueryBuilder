<?php

namespace Saraf\QB\QueryBuilder\Capability;

use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;
use Saraf\QB\QueryBuilder\Helpers\Escape;

trait From
{
    use Escape;

    private string $fromTable;

    /**
     * @throws QueryBuilderException
     */
    public function from(string $table): static
    {
        if (empty(trim($table)))
            throw new QueryBuilderException("from is required");

        $this->fromTable = $this->keyEscape($table);

        return $this;
    }
}