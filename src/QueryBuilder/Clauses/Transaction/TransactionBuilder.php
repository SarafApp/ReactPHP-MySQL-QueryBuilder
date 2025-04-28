<?php

namespace Saraf\QB\QueryBuilder\Clauses\Transaction;

use Saraf\QB\QueryBuilder\Contracts\Transaction\TransactionBuilderContract;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;

class TransactionBuilder implements TransactionBuilderContract
{
    public function __construct(
        protected DBFactory $factory
    )
    {
    }

    /**
     * @throws DBFactoryException
     */
    public function startTransaction(): \React\Promise\PromiseInterface
    {
        return $this->factory->query("START TRANSACTION");
    }

    /**
     * @throws DBFactoryException
     */
    public function rollback(): \React\Promise\PromiseInterface
    {
        return $this->factory->query("ROLLBACK");
    }


    /**
     * @throws DBFactoryException
     */
    public function commit(): \React\Promise\PromiseInterface
    {
        return $this->factory->query("COMMIT");
    }
}