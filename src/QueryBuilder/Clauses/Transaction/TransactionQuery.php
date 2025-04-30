<?php

namespace Saraf\QB\QueryBuilder\Clauses\Transaction;

use Saraf\QB\QueryBuilder\Contracts\Transaction\TransactionQueryContract;
use Saraf\QB\QueryBuilder\Exceptions\TransactionException;

class TransactionQuery implements TransactionQueryContract
{
    /**
     * @throws TransactionException
     */
    public function rollback(?string $message = null)
    {
        throw new TransactionException("Transaction Rolled Back" . !empty($message) && " With message : {$message}");
    }
}