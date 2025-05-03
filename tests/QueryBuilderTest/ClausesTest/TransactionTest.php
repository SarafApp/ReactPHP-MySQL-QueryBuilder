<?php

namespace QueryBuilderTest\ClausesTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Clauses\Select;
use Saraf\QB\QueryBuilder\Clauses\Transaction;
use Saraf\QB\QueryBuilder\Core\Query;
use Saraf\QB\QueryBuilder\Exceptions\TransactionException;
use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResult;
use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResultCollection;

final class TransactionTest extends TestCase
{
    /**
     * @test
     */
    public function checkQueryAddedToTransaction()
    {
        $transaction = new Transaction();

        $addedQuery = $transaction->addQuery(
            'test',
            new Select(),
            function (QueryResult $result, QueryResultCollection $collection) {
            }
        );

        $reflection = new \ReflectionClass($transaction);

        $queries = $reflection->getProperty("queries");
        $queries->setAccessible(true);

        self::assertEquals("test", $queries->getValue($transaction)[0]['name']);
    }

    /**
     * @test
     */
    public function checkSetInstanceOfQueryResultCollection()
    {
        $transaction = new Transaction();

        $reflection = new \ReflectionClass($transaction);

        $queryResultCollection = $reflection->getProperty("queryResultCollection");
        $queryResultCollection->setAccessible(true);

        self::assertInstanceOf(QueryResultCollection::class, $queryResultCollection->getValue($transaction));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfCompileCalledOnNoQuery()
    {
        $transaction = new Transaction();

        self::expectException(TransactionException::class);

        $transaction->compile();
    }

    /**
     * @test
     */
    public function canCallCompileFromQuery()
    {
        $transaction = new Transaction();

        $addedQuery = $transaction->addQuery(
            'test',
            (new Select())
                ->from("users")
                ->addAllColumns(),
            function (QueryResult $result, QueryResultCollection $collection) {
            }
        );

        $reflection = new \ReflectionClass($transaction);

        $queries = $reflection->getProperty("queries");
        $queries->setAccessible(true);
        $targetQuery = $queries->getValue($transaction)[0]['query'];

        self::assertInstanceOf(Query::class, $targetQuery->compile());
    }

    /**
     * @test
     */
    public function isValidQueryStringInsideTransaction()
    {
        $transaction = new Transaction();

        $addedQuery = $transaction->addQuery(
            'test',
            (new Select())
                ->from("users")
                ->addAllColumns(),
            function (QueryResult $result, QueryResultCollection $collection) {
            }
        );

        $reflection = new \ReflectionClass($transaction);

        $queries = $reflection->getProperty("queries");
        $queries->setAccessible(true);
        $targetQuery = $queries->getValue($transaction)[0]['query'];

        self::assertEquals(
            "SELECT * FROM `users` ",
            $targetQuery->compile()->getQuery()
        );
    }
}