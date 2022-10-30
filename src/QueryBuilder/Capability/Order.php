<?php

namespace Saraf\QB\QueryBuilder\Capability;

use Saraf\QB\QueryBuilder\Enums\OrderDirection;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;
use Saraf\QB\QueryBuilder\Helpers\Escape;

trait Order
{
    use Escape;

    private array $orderBy = [];

    /**
     * @throws QueryBuilderException
     */
    public function addOrder(string $orderColumn, OrderDirection|string $orderDirection = OrderDirection::None, bool $escape = true): self
    {
        if (empty(trim($orderColumn))) {
            throw new QueryBuilderException("OrderColumn cant set as empty string");
        }

        if (!in_array($orderDirection, [OrderDirection::Descending, OrderDirection::Ascending, OrderDirection::None], true)) {
            throw new QueryBuilderException("OrderDirection Not Valid");
        }

        if ($escape) {
            $orderColumn = $this->keyEscape($orderColumn);
        }

        $this->orderBy[$orderColumn] = $orderDirection;
        return $this;
    }

    public function addRandomOrder(): self
    {
        $this->orderBy['RAND()'] = '';

        return $this;
    }
}
