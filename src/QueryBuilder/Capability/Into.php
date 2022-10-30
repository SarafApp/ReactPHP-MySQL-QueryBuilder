<?php

namespace Saraf\QB\QueryBuilder\Capability;

use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;
use Saraf\QB\QueryBuilder\Helpers\Escape;

trait Into
{
    use Escape;

    private string $intoTable;

    /**
     * @throws QueryBuilderException
     */
    public function into(string $table): static
    {
        if (empty(trim($table)))
            throw new QueryBuilderException("Into is Required");

        $this->intoTable = $this->keyEscape($table);

        return $this;
    }
}