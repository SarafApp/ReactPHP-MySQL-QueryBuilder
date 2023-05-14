<?php

namespace QueryBuilderTest\HelpersTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;
use Saraf\QB\QueryBuilder\Helpers\Escape;

final class EscapeTest extends TestCase
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function keyEscapeWorks()
    {
        $e = new class () {
            use Escape;
        };

        $reflection = new \ReflectionMethod($e, "keyEscape");
        $reflection->setAccessible(true);

        self::assertEquals("`Username`", $reflection->invoke($e, 'Username'));
        self::assertEquals("`Trades`.`amount`", $reflection->invoke($e, 'Trades.amount'));
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function keyEscapeEmptyException()
    {
        $e = new class () {
            use Escape;
        };

        $reflection = new \ReflectionMethod($e, "keyEscape");
        $reflection->setAccessible(true);


        self::expectException(QueryBuilderException::class);
        $reflection->invoke($e, '');
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function keyEscapeStar()
    {
        $e = new class () {
            use Escape;
        };

        $reflection = new \ReflectionMethod($e, "keyEscape");
        $reflection->setAccessible(true);


        self::assertEquals("*.*", $reflection->invoke($e, '*.*'));
        self::assertEquals("*", $reflection->invoke($e, '*'));
        self::assertEquals("`Trades`.*", $reflection->invoke($e, 'Trades.*'));
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function escapeNull()
    {
        $e = new class () {
            use Escape;
        };

        $reflection = new \ReflectionMethod($e, "escape");
        $reflection->setAccessible(true);


        self::assertEquals("NULL", $reflection->invoke($e, null));
        self::assertEquals("''", $reflection->invoke($e, ""));
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function escapeInteger()
    {
        $e = new class () {
            use Escape;
        };

        $reflection = new \ReflectionMethod($e, "escape");
        $reflection->setAccessible(true);


        self::assertEquals("10000", $reflection->invoke($e, 10000));
        self::assertEquals("12423040.23894234", $reflection->invoke($e, 12423040.23894234));
        self::assertEquals("-3.141516923874", $reflection->invoke($e, -3.141516923874));
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function escapeBooleans()
    {
        $e = new class () {
            use Escape;
        };

        $reflection = new \ReflectionMethod($e, "escape");
        $reflection->setAccessible(true);


        self::assertEquals("1", $reflection->invoke($e, true));
        self::assertEquals("0", $reflection->invoke($e, false));
        self::assertEquals("1", $reflection->invoke($e, true === true));

        self::assertEquals("0", $reflection->invoke($e, boolval([])));
        self::assertEquals("1", $reflection->invoke($e, boolval([5])));

        self::assertEquals("'true'", $reflection->invoke($e, "true"));
        self::assertEquals("'false'", $reflection->invoke($e, "false"));
    }


    /**
     * @test
     * @throws \ReflectionException
     */
    public function escapeString()
    {
        $e = new class () {
            use Escape;
        };

        $reflection = new \ReflectionMethod($e, "escape");
        $reflection->setAccessible(true);

        self::assertEquals("'\\0'", $reflection->invoke($e, "\00"));
        self::assertEquals("'\''", $reflection->invoke($e, "'"));
        self::assertEquals("'\\\"'", $reflection->invoke($e, "\""));
        self::assertEquals("'\\\\'", $reflection->invoke($e, "\\"));
    }
}
