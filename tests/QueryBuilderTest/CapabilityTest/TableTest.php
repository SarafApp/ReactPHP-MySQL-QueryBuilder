<?php

namespace QueryBuilderTest\CapabilityTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Capability\Table;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

class TableTest extends TestCase
{
    /**
     * @test
     */
    public function throwExceptionOnEmptyString()
    {
        $t = new class () {
            use Table;
        };

        self::expectException(QueryBuilderException::class);
        $t->table("");
    }

    /**
     * @test
     */
    public function validateValueOfTable()
    {
        $t = new class () {
            use Table;
        };

        $t->table("Users");

        $reflection = new \ReflectionClass($t);

        $updateTable = $reflection->getProperty("updateTable");
        $updateTable->setAccessible(true);
        self::assertEquals("`Users`", $updateTable->getValue($t));
    }

    /**
     * @test
     */
    public function updateTableNotInit()
    {
        $t = new class () {
            use Table;
        };

        $reflection = new \ReflectionClass($t);

        $updateTable = $reflection->getProperty("updateTable");
        $updateTable->setAccessible(true);
        self::assertFalse($updateTable->isInitialized($t));
    }


    /**
     * @test
     */
    public function hasUpdateTableAttribute()
    {
        $t = new class () {
            use Table;
        };

        self::assertObjectHasAttribute("updateTable", $t);
    }
}
