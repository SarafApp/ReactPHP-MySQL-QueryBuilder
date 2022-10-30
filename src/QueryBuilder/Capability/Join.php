<?php

namespace Saraf\QB\QueryBuilder\Capability;

use Saraf\QB\QueryBuilder\Enums\JoinDirection;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;
use Saraf\QB\QueryBuilder\Helpers\Escape;

trait Join
{
    use Escape;

    private array $joins = [];

    /**
     * @throws QueryBuilderException
     */
    public function leftJoin(string $table, string $fromON, string $toON, bool $escapeON = true): static
    {
        return $this->join(JoinDirection::Left, $table, $fromON, $toON, $escapeON);
    }

    /**
     * @throws QueryBuilderException
     */
    public function rightJoin(string $table, string $fromON, string $toON, bool $escapeON = true): static
    {
        return $this->join(JoinDirection::Right, $table, $fromON, $toON, $escapeON);
    }

    /**
     * @throws QueryBuilderException
     */
    public function innerJoin(string $table, string $fromON, string $toON, bool $escapeON = true): static
    {
        return $this->join(JoinDirection::Inner, $table, $fromON, $toON, $escapeON);
    }

    /**
     * @throws QueryBuilderException
     */
    public function fullJoin(string $table, string $fromON, string $toON, bool $escapeON = true): static
    {
        return $this->join(JoinDirection::Full, $table, $fromON, $toON, $escapeON);
    }

    /**
     * @throws QueryBuilderException
     */
    protected function join(string $joinType, string $table, string $fromON, string $toON, bool $escapeON = true): static
    {
        if (empty(trim($table)))
            throw new QueryBuilderException("Table is required");

        $table = $this->keyEscape($table);
        if ($escapeON) {
            $fromON = $this->keyEscape($fromON);
            $toON = $this->keyEscape($toON);
        }

        $this->joins[] = sprintf("%s JOIN %s ON %s = %s", $joinType, $table, $fromON, $toON);

        return $this;
    }
}
