<?php

namespace QueryBuilderTest\HelpersTest\QueryResultTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResult;

final class QueryResultTest extends TestCase
{
    /**
     * @test
     */
    public function canGetInstance()
    {
        $qr = new QueryResult(true);

        self::assertInstanceOf(QueryResult::class, $qr);
    }

    /**
     * @test
     */
    public function validateQueryResultProperties()
    {
        $qr = new QueryResult(
            true,
            10,
            [
                ['id' => 1],
            ],
            1,
            10
        );

        $reflection = new \ReflectionClass($qr);

        $result = $reflection->getProperty("result");
        $result->setAccessible(true);

        $count = $reflection->getProperty("count");
        $count->setAccessible(true);

        $rows = $reflection->getProperty("rows");
        $rows->setAccessible(true);

        $affectedRows = $reflection->getProperty("affectedRows");
        $affectedRows->setAccessible(true);

        $insertId = $reflection->getProperty("insertId");
        $insertId->setAccessible(true);

        self::assertTrue($result->getValue($qr));
        self::assertEquals(10, $count->getValue($qr));
        self::assertEquals([
            ['id' => 1],
        ], $rows->getValue($qr));
        self::assertEquals(1, $affectedRows->getValue($qr));
        self::assertEquals(10, $insertId->getValue($qr));
    }
}