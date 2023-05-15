<?php

namespace QueryBuilderTest\CapabilityTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class GroupTest extends TestCase
{
    /**
     * @test
     */
    public function groupByAttributeExists()
    {
        $g = new class () {
            use \Saraf\QB\QueryBuilder\Capability\Group;
        };
        self::assertTrue(property_exists($g, 'groupBy'));
    }

    /**
     * @test
     */
    public function groupByEscapeBackTick()
    {
        $g = new class () {
            use \Saraf\QB\QueryBuilder\Capability\Group;
        };

        $g->groupBy("roles");

        $reflection = new \ReflectionClass($g);

        $groupBy = $reflection->getProperty("groupBy");
        $groupBy->setAccessible(true);
        self::assertEquals(["`roles`"], $groupBy->getValue($g));
    }

    /**
     * @test
     */
    public function groupByNoEscape()
    {
        $g = new class () {
            use \Saraf\QB\QueryBuilder\Capability\Group;
        };

        $g->groupBy(["roles", "username"], false);

        $reflection = new \ReflectionClass($g);

        $groupBy = $reflection->getProperty("groupBy");
        $groupBy->setAccessible(true);
        self::assertEquals(["roles", "username"], $groupBy->getValue($g));
    }

    /**
     * @test
     */
    public function groupByArrayEscape()
    {
        $g = new class () {
            use \Saraf\QB\QueryBuilder\Capability\Group;
        };

        $g->groupBy(["roles", "username"]);

        $reflection = new \ReflectionClass($g);

        $groupBy = $reflection->getProperty("groupBy");
        $groupBy->setAccessible(true);
        self::assertEquals(["`roles`", "`username`"], $groupBy->getValue($g));
    }

    /**
     * @test
     */
    public function raiseExceptionWithEmptyString()
    {
        $g = new class () {
            use \Saraf\QB\QueryBuilder\Capability\Group;
        };

        self::expectException(QueryBuilderException::class);
        $g->groupBy("");
    }

    /**
     * @test
     */
    public function raiseExceptionWithEmptyArray()
    {
        $g = new class () {
            use \Saraf\QB\QueryBuilder\Capability\Group;
        };

        self::expectException(QueryBuilderException::class);
        $g->groupBy([]);
    }


    /**
     * @test
     */
    public function groupByAttributeIsNotInit()
    {
        $g = new class () {
            use \Saraf\QB\QueryBuilder\Capability\Group;
        };
        $reflection = new \ReflectionClass($g);

        $groupBy = $reflection->getProperty("groupBy");
        $groupBy->setAccessible(true);
        self::assertEquals([], $groupBy->getValue($g));
        self::assertCount(0, $groupBy->getValue($g));
    }

    /**
     * @test
     */
    public function traitHasGroupByMethod()
    {
        $reflection = new \ReflectionClass(\Saraf\QB\QueryBuilder\Capability\Group::class);
        self::assertTrue($reflection->hasMethod("groupBy"));
    }
}
