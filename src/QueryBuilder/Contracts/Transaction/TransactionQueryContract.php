<?php

namespace Saraf\QB\QueryBuilder\Contracts\Transaction;

interface TransactionQueryContract
{
    public function rollback(?string $message = null);
}