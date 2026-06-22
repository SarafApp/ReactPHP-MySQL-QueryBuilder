<?php

namespace Saraf\QB\QueryBuilder\Core;

use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class TxBuilder
{
    public function __construct(
        protected object   $inner,
        protected DBWorker $reservedWorker
    )
    {
    }

    public function __call(string $name, array $args): mixed
    {
        $result = $this->inner->$name(...$args);
        return $result === $this->inner ? $this : $result;
    }

    /**
     * @throws QueryBuilderException
     */
    public function compile(): PinnedEQuery
    {
        $compiled = $this->inner->compile();
        return new PinnedEQuery($compiled->getQueryAsString(), $this->reservedWorker);
    }
}
