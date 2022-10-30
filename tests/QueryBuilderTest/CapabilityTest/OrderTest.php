<?php

namespace QueryBuilderTest\CapabilityTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Capability\Order;
use Saraf\QB\QueryBuilder\Enums\OrderDirection;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class OrderTest extends TestCase
{
    /**
     * @test
     */
    public function orderDirectionNotValid()
    {
        $o = new class () {
            use Order;
        };

        self::expectException(QueryBuilderException::class);
        $o->addOrder("price", "PPP");
    }

    /**
     * @test
     */
    public function orderColumnEmpty()
    {
        $o = new class () {
            use Order;
        };

        self::expectException(QueryBuilderException::class);
        $o->addOrder("", OrderDirection::Ascending);
    }

    /**
     * @test
     */
    public function emptyOrderDirection()
    {
        $o = new class () {
            use Order;
        };

        $o->addOrder("users", "");

        $reflection = new \ReflectionClass($o);

        $orderBy = $reflection->getProperty("orderBy");
        $orderBy->setAccessible(true);
        self::assertEquals(["`users`" => ""], $orderBy->getValue($o));
    }

    /**
     * @test
     */
    public function isOrderColumnExists()
    {
        $o = new class () {
            use Order;
        };

        $o->addOrder("users", "");

        $reflection = new \ReflectionClass($o);

        $orderBy = $reflection->getProperty("orderBy");
        $orderBy->setAccessible(true);
        self::assertTrue(key_exists("`users`", $orderBy->getValue($o)));
    }

    /**
     * @test
     */
    public function orderColumnNoEscape()
    {
        $o = new class () {
            use Order;
        };

        $o->addOrder("users", "", false);

        $reflection = new \ReflectionClass($o);

        $orderBy = $reflection->getProperty("orderBy");
        $orderBy->setAccessible(true);
        self::assertTrue(key_exists("users", $orderBy->getValue($o)));
    }

    /**
     * @test
     */
    public function randomOrderTest()
    {
        $o = new class () {
            use Order;
        };

        $o->addRandomOrder();

        $reflection = new \ReflectionClass($o);

        $orderBy = $reflection->getProperty("orderBy");
        $orderBy->setAccessible(true);
        self::assertTrue(key_exists("RAND()", $orderBy->getValue($o)));
    }
}
