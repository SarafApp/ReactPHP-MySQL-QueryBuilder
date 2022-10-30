<?php

namespace Saraf\QB\QueryBuilder\Capability;

use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

trait Limit
{
    private int $offset;
    private int $count;

    /**
     * @throws QueryBuilderException
     */
    public function setOffset(int|float $offset): static
    {
        if (isset($this->offset))
            throw new QueryBuilderException("Offset Already Set");

        $this->offset = $offset;

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function setLimit(int|float $count): static
    {
        if (isset($this->count))
            throw new QueryBuilderException("Count Already Set");

        $this->count = $count;

        return $this;
    }
}
