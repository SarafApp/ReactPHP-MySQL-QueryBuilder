<?php

namespace Saraf\QB\QueryBuilder\Clauses\Transaction;

use Saraf\QB\QueryBuilder\Contracts\Transaction\TransactionQueryContract;
use Saraf\QB\QueryBuilder\Exceptions\TransactionException;

class TransactionQuery implements TransactionQueryContract
{
    /**
     * @throws TransactionException
     */
    public function rollback()
    {
        throw new TransactionException("Transaction Rolled Back");
    }
}