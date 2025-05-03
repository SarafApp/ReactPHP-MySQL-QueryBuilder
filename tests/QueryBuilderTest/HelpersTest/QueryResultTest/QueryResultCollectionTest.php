<?php

namespace QueryBuilderTest\HelpersTest\QueryResultTest;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Saraf\QB\QueryBuilder\Exceptions\QueryResultException;
use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResult;
use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResultCollection;
use TypeError;

final class QueryResultCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function checkOlyAcceptQueryResult()
    {
        $qrc = new QueryResultCollection();

        self::expectException(TypeError::class);

        $qrc->add('test', ['data' => 'test']);
    }

    /**
     * @test
     */
    public function checkQueryResultAddedToQueryResultCollection()
    {
        $qrc = new QueryResultCollection();

        $queryResult = new QueryResult(true);

        $qrc->add('test', $queryResult);

        $reflection = new ReflectionClass($qrc);

        $queryResults = $reflection->getProperty("queryResults");
        $queryResults->setAccessible(true);

        self::assertArrayHasKey('test', $queryResults->getValue($qrc));
        self::assertEquals($queryResult, $queryResults->getValue($qrc)['test']);
    }

    /**
     * @test
     */
    public function canGetQueryResultByNameFromCollection()
    {
        $qrc = new QueryResultCollection();

        $queryResult = new QueryResult(true);

        $qrc->add('test', $queryResult);

        self::assertEquals($queryResult, $qrc->get('test'));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfNameNotExistsInCollection()
    {
        $qrc = new QueryResultCollection();

        $queryResult = new QueryResult(true);

        $qrc->add('test', $queryResult);

        self::expectException(QueryResultException::class);

        $qrc->get('wrong_name');
    }

    /**
     * @test
     */
    public function canTraverseQueryResultCollectionClass()
    {
        $qrc = new QueryResultCollection();

        $queryResult = new QueryResult(true);

        $qrc->add('test', $queryResult);

        $expectQueryResult = null;

        foreach ($qrc as $qr) {
            $expectQueryResult = $qr;
        }

        self::assertEquals($expectQueryResult, $queryResult);
    }
}