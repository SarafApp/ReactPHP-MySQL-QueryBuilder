<?php

namespace QueryBuilderTest\CapabilityTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Capability\Limit;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class LimitTest extends TestCase
{
    /**
     * @test
     */
    public function setOffsetTwiceShouldRaiseException()
    {
        $l = new class () {
            use Limit;
        };


        $l->setOffset(10);

        self::expectException(QueryBuilderException::class);

        $l->setOffset(20);
    }

    /**
     * @test
     */
    public function setLimitTwiceShouldRaiseException()
    {
        $l = new class () {
            use Limit;
        };


        $l->setLimit(10);

        self::expectException(QueryBuilderException::class);

        $l->setLimit(20);
    }

    /**
     * @test
     */
    public function isOffsetStoredCorrectly()
    {
        $l = new class () {
            use Limit;
        };

        $l->setOffset(10);

        $reflection = new \ReflectionClass($l);

        $offset = $reflection->getProperty("offset");
        $offset->setAccessible(true);
        self::assertEquals(10, $offset->getValue($l));
    }

    /**
     * @test
     */
    public function isLimitStoredCorrectly()
    {
        $l = new class () {
            use Limit;
        };

        $l->setLimit(10);

        $reflection = new \ReflectionClass($l);

        $limit = $reflection->getProperty("count");
        $limit->setAccessible(true);
        self::assertEquals(10, $limit->getValue($l));
    }

    /**
     * @test
     */
    public function limitNotInitAtStart()
    {
        $l = new class () {
            use Limit;
        };


        $reflection = new \ReflectionClass($l);

        $limit = $reflection->getProperty("count");
        $limit->setAccessible(true);
        self::assertFalse($limit->isInitialized($l));
    }

    /**
     * @test
     */
    public function offsetNotInitAtStart()
    {
        $l = new class () {
            use Limit;
        };


        $reflection = new \ReflectionClass($l);

        $offset = $reflection->getProperty("offset");
        $offset->setAccessible(true);
        self::assertFalse($offset->isInitialized($l));
    }
}
