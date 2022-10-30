<?php

namespace QueryBuilderTest\CapabilityTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

use function PHPUnit\Framework\assertEquals;

final class IntoTest extends TestCase
{
    /**
     * @test
     */
    public function raiseExceptionWithEmptyValue()
    {
        $i = new class () {
            use \Saraf\QB\QueryBuilder\Capability\Into;
        };

        self::expectException(QueryBuilderException::class);
        $i->into("");
    }

    public function isIntoTableEscaped()
    {
        $i = new class () {
            use \Saraf\QB\QueryBuilder\Capability\Into;
        };

        $i->into("Users");

        $reflection = new \ReflectionClass($i);

        $intoTable = $reflection->getProperty("intoTable");
        $intoTable->setAccessible(true);

        assertEquals("`Users`", $intoTable->getValue());
    }

    /**
     * @test
     */
    public function traitHasIntoMethod()
    {
        $reflection = new \ReflectionClass(\Saraf\QB\QueryBuilder\Capability\Into::class);
        self::assertTrue($reflection->hasMethod("into"));
    }
}
