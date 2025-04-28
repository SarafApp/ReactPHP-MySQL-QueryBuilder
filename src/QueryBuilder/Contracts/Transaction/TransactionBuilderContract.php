<?php

namespace Saraf\QB\QueryBuilder\Contracts\Transaction;

interface TransactionBuilderContract
{
    public function startTransaction(): \React\Promise\PromiseInterface;

    public function rollback(): \React\Promise\PromiseInterface;

    public function commit(): \React\Promise\PromiseInterface;
}