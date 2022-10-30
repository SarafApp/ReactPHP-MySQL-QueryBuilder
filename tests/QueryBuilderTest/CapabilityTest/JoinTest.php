<?php

namespace QueryBuilderTest\CapabilityTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Capability\Join;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class JoinTest extends TestCase
{
    /**
     * @test
     */
    public function leftJoinThrowExceptionOnEmptyString()
    {
        $j = new class () {
            use Join;
        };

        self::expectException(QueryBuilderException::class);
        $j->leftJoin("", "", "");
    }

    /**
     * @test
     */
    public function leftJoinTableWithEscape()
    {
        $j = new class () {
            use Join;
        };

        $j->leftJoin("Users", "Trades.user", "Users.id");

        $reflection = new \ReflectionClass($j);

        $join = $reflection->getProperty("joins");
        $join->setAccessible(true);
        self::assertCount(1, $join->getValue($j));
        self::assertEquals("LEFT JOIN `Users` ON `Trades`.`user` = `Users`.`id`", $join->getValue($j)[0]);
    }

    /**
     * @test
     */
    public function leftJoinTableWithoutEscape()
    {
        $j = new class () {
            use Join;
        };

        $j->leftJoin("Users", "Trades.user", "Users.id", false);

        $reflection = new \ReflectionClass($j);

        $join = $reflection->getProperty("joins");
        $join->setAccessible(true);
        self::assertCount(1, $join->getValue($j));
        self::assertEquals("LEFT JOIN `Users` ON Trades.user = Users.id", $join->getValue($j)[0]);
    }

    /**
     * @test
     */
    public function rightJoinThrowExceptionOnEmptyString()
    {
        $j = new class () {
            use Join;
        };

        self::expectException(QueryBuilderException::class);
        $j->rightJoin("", "", "");
    }

    /**
     * @test
     */
    public function innerJoinThrowExceptionOnEmptyString()
    {
        $j = new class () {
            use Join;
        };

        self::expectException(QueryBuilderException::class);
        $j->innerJoin("", "", "");
    }

    /**
     * @test
     */
    public function fullJoinThrowExceptionOnEmptyString()
    {
        $j = new class () {
            use Join;
        };

        self::expectException(QueryBuilderException::class);
        $j->fullJoin("", "", "");
    }
}
