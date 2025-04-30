<?php

namespace Saraf\QB\QueryBuilder\Clauses\Transaction;

use React\Promise\PromiseInterface;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;
use Saraf\QB\QueryBuilder\Exceptions\TransactionException;

class Transaction extends TransactionBuilder
{
    /**
     * @throws DBFactoryException
     */
    public function __invoke(
        callable $body
    ): \React\Promise\PromiseInterface
    {
        return $this->startTransaction()
            ->then(function () use ($body) {

                $bodyPromise = $body(new TransactionQuery());

                if (!$bodyPromise instanceof PromiseInterface) {

                    throw new TransactionException("Transaction body should return promise");
                }

                return $bodyPromise;

            })->then(function () {

                $this->commit();

            })->catch(function (\Throwable|\Exception $e) {

                $this->rollback();
                exit(0);

            });
    }
}