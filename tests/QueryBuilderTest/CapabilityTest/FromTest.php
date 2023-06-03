<?php

namespace QueryBuilderTest\CapabilityTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class FromTest extends TestCase
{
    /**
     * @test
     */
    public function fromTableAttributeExists()
    {
        $f = new class () {
            use \Saraf\QB\QueryBuilder\Capability\From;
        };
        self::assertTrue(property_exists($f, 'fromTable'));
    }

    /**
     * @test
     */
    public function fromTableAttributeIsNotInit()
    {
        $f = new class () {
            use \Saraf\QB\QueryBuilder\Capability\From;
        };
        $reflection = new \ReflectionClass($f);

        $fromTable = $reflection->getProperty("fromTable");
        $fromTable->setAccessible(true);
        self::assertFalse($fromTable->isInitialized($f));
    }

    /**
     * @test
     */
    public function fromTableAttributeHasCorrectValue()
    {
        $f = new class () {
            use \Saraf\QB\QueryBuilder\Capability\From;
        };

        $f->from("Users");
        $f->from("Trades");
        $f->from("Phones");

        $reflection = new \ReflectionClass($f);

        $fromTable = $reflection->getProperty("fromTable");
        $fromTable->setAccessible(true);
        self::assertEquals("`Phones`", $fromTable->getValue($f));
    }


    /**
     * @test
     */
    public function raiseExceptionWithEmptyValue()
    {
        $f = new class () {
            use \Saraf\QB\QueryBuilder\Capability\From;
        };

        self::expectException(QueryBuilderException::class);
        $f->from("");
    }

    /**
     * @test
     */
    public function traitHasFromMethod()
    {
        $reflection = new \ReflectionClass(\Saraf\QB\QueryBuilder\Capability\From::class);
        self::assertTrue($reflection->hasMethod("from"));
    }
}
