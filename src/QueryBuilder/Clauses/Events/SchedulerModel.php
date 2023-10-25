<?php

namespace Saraf\QB\QueryBuilder\Clauses\Events;

class SchedulerModel
{
    protected int $amount;
    protected string $periods;

    public function __construct(int $amount, string $periods)
    {
        $this->amount = $amount;
        $this->periods = $periods;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getPeriods(): string
    {
        return $this->periods;
    }

    public function setPeriods(string $periods): void
    {
        $this->periods = $periods;
    }
}