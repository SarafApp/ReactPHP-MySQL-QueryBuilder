<?php

namespace QueryBuilderTest\CapabilityTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Capability\Where;

final class WhereTest extends TestCase
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereEscape()
    {
        $w = new class () {
            use Where;
        };

        $w->where("name", "John", true, true);
        $w->where("family", "Doe");

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(2, $whereStatements->getValue($w)[0]);
        self::assertEquals("`name` = 'John'", $whereStatements->getValue($w)[0][0]);
        self::assertEquals("`family` = 'Doe'", $whereStatements->getValue($w)[0][1]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereNoEscapeKey()
    {
        $w = new class () {
            use Where;
        };

        $w->where("age", 18, false, false);
        $w->where("startTime", 1666842836.00, false, false);
        $w->where("endTime", "1666842836.00", true, true);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(3, $whereStatements->getValue($w)[0]);
        self::assertEquals("age = 18", $whereStatements->getValue($w)[0][0]);
        self::assertEquals("startTime = 1666842836", $whereStatements->getValue($w)[0][1]);
        self::assertEquals("`endTime` = '1666842836.00'", $whereStatements->getValue($w)[0][2]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereTrueValue()
    {
        $w = new class () {
            use Where;
        };

        $w->where("isSent", true);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(1, $whereStatements->getValue($w)[0]);
        self::assertEquals("`isSent` = 1", $whereStatements->getValue($w)[0][0]);
    }

    /**
     * @test
     */
    public function testWhereNotEqual()
    {
        $w = new class () {
            use Where;
        };

        $w->whereNotEqual("isSent", true);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(1, $whereStatements->getValue($w)[0]);
        self::assertEquals("`isSent` != 1", $whereStatements->getValue($w)[0][0]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereIn()
    {
        $w = new class () {
            use Where;
        };

        $w->whereIn("status", ["APPROVED", "PENDING"]);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(1, $whereStatements->getValue($w)[0]);
        self::assertEquals("`status` IN ('APPROVED','PENDING')", $whereStatements->getValue($w)[0][0]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereNotIn()
    {
        $w = new class () {
            use Where;
        };

        $w->whereNotIn("status", ["APPROVED", "PENDING"]);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(1, $whereStatements->getValue($w)[0]);
        self::assertEquals("`status` NOT IN ('APPROVED','PENDING')", $whereStatements->getValue($w)[0][0]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereGreater()
    {
        $w = new class () {
            use Where;
        };

        $w->whereGreater("age", 23);
        $w->whereGreater("office", 10, true);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(2, $whereStatements->getValue($w)[0]);
        self::assertEquals("`age` > 23", $whereStatements->getValue($w)[0][0]);
        self::assertEquals("`office` >= 10", $whereStatements->getValue($w)[0][1]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereLesser()
    {
        $w = new class () {
            use Where;
        };

        $w->whereLesser("age", 23);
        $w->whereLesser("office", 10, true);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(2, $whereStatements->getValue($w)[0]);
        self::assertEquals("`age` < 23", $whereStatements->getValue($w)[0][0]);
        self::assertEquals("`office` <= 10", $whereStatements->getValue($w)[0][1]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereBetween()
    {
        $w = new class () {
            use Where;
        };

        $w->whereBetween("age", 23, 32);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(1, $whereStatements->getValue($w)[0]);
        self::assertEquals("`age` BETWEEN 23 AND 32", $whereStatements->getValue($w)[0][0]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereNotBetween()
    {
        $w = new class () {
            use Where;
        };

        $w->whereNotBetween("age", 23, 32);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(1, $whereStatements->getValue($w)[0]);
        self::assertEquals("`age` NOT BETWEEN 23 AND 32", $whereStatements->getValue($w)[0][0]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereIsNull()
    {
        $w = new class () {
            use Where;
        };

        $w->whereIsNull("age");

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(1, $whereStatements->getValue($w)[0]);
        self::assertEquals("`age` IS NULL", $whereStatements->getValue($w)[0][0]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereIsNotNull()
    {
        $w = new class () {
            use Where;
        };

        $w->whereIsNotNull("age");

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(1, $whereStatements->getValue($w)[0]);
        self::assertEquals("`age` IS NOT NULL", $whereStatements->getValue($w)[0][0]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereLike()
    {
        $w = new class () {
            use Where;
        };

        $w->whereLike("name", "h_", true, false);
        $w->whereLike("family", "pa", true, true, false, false);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(2, $whereStatements->getValue($w)[0]);
        self::assertEquals("`name` LIKE '%h_'", $whereStatements->getValue($w)[0][0]);
        self::assertEquals("family LIKE '%'.pa.'%'", $whereStatements->getValue($w)[0][1]);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereNotLike()
    {
        $w = new class () {
            use Where;
        };

        $w->whereNotLike("name", "h_", true, false);
        $w->whereNotLike("family", "pa", true, true);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(2, $whereStatements->getValue($w)[0]);
        self::assertEquals("`name` NOT LIKE '%h_'", $whereStatements->getValue($w)[0][0]);
        self::assertEquals("`family` NOT LIKE '%pa%'", $whereStatements->getValue($w)[0][1]);
    }


    /**
     * @test
     * @throws \ReflectionException
     */
    public function testWhereGroup()
    {
        $w = new class () {
            use Where;
        };

        $w->whereGroup([
            "name" => "John",
            "family" => "Doe'"
        ]);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);

        self::assertCount(1, $whereStatements->getValue($w));
        self::assertCount(2, $whereStatements->getValue($w)[0]);
        self::assertEquals("`name` = 'John'", $whereStatements->getValue($w)[0][0]);
        self::assertEquals("`family` = 'Doe'''", $whereStatements->getValue($w)[0][1]);
    }

    /**
     * @throws \ReflectionException
     */
    public function testOrGroupTogether()
    {
        $w = new class () {
            use Where;
        };

        $w->whereGroup([
            "name" => "John",
            "family" => "Doe"
        ]);

        $w->or();

        $w->whereGroup([
            "name" => "Mohammad",
            "family" => "Mohammadi",
            "isSent" => true
        ]);

        $reflection = new \ReflectionClass($w);

        $whereStatements = $reflection->getProperty("whereStatements");
        $whereStatements->setAccessible(true);


        // First Part
        self::assertCount(2, $whereStatements->getValue($w));
        self::assertCount(2, $whereStatements->getValue($w)[0]);
        self::assertEquals("`name` = 'John'", $whereStatements->getValue($w)[0][0]);
        self::assertEquals("`family` = 'Doe'", $whereStatements->getValue($w)[0][1]);

        //Second Part
        self::assertCount(2, $whereStatements->getValue($w));
        self::assertCount(3, $whereStatements->getValue($w)[1]);
        self::assertEquals("`name` = 'Mohammad'", $whereStatements->getValue($w)[1][0]);
        self::assertEquals("`family` = 'Mohammadi'", $whereStatements->getValue($w)[1][1]);
        self::assertEquals("`isSent` = 1", $whereStatements->getValue($w)[1][2]);
    }
}
