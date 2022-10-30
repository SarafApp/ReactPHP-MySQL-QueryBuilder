<?php

namespace QueryBuilderTest\ClausesTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Clauses\Insert;
use Saraf\QB\QueryBuilder\Core\Query;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class InsertTest extends TestCase
{
    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function setColumnsWorks()
    {
        $i = new Insert();

        $i->setColumns([
            'name', 'family', 'address'
        ], false)->setColumns([
            'phone', 'age'
        ], true);

        $reflection = new \ReflectionClass($i);

        $columns = $reflection->getProperty("columns");
        $columns->setAccessible(true);

        self::assertCount(5, $columns->getValue($i));
        self::assertEquals("name", $columns->getValue($i)[0]);
        self::assertEquals("`phone`", $columns->getValue($i)[3]);
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function compileShouldReturnQuery()
    {
        $i = new Insert();

        $query = $i->into("Users")
            ->setColumns([
                "name", "family", "location"
            ])->addRow([
                "John", "Doe", "Iran"
            ])->compile();

        self::assertInstanceOf(Query::class, $query);

        self::assertEquals(
            "INSERT INTO `Users` (`name`,`family`,`location`) VALUES ('John','Doe','Iran')",
            $query->getQuery()
        );
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function intoTableEmpty()
    {
        $i = new Insert();

        self::expectException(QueryBuilderException::class);
        $i->setColumns([
            "name", "family"
        ])->addRow([
            "John", "Doe"
        ])->compile();
    }


    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function columnsAreEmpty()
    {
        $i = new Insert();

        self::expectException(QueryBuilderException::class);
        $i->into("Users")->addRow([
            "John", "Doe"
        ])->compile();
    }


    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function rowsAreEmpty()
    {
        $i = new Insert();

        self::expectException(QueryBuilderException::class);
        $i->into("Users")->setColumns([
            "name", "family"
        ])->compile();
    }
}
