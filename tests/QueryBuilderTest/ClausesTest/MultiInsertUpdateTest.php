<?php

namespace QueryBuilderTest\ClausesTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Clauses\MultiInsertUpdate;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class MultiInsertUpdateTest extends TestCase
{
    public function testInsertAliasEscape()
    {
        $miu = new MultiInsertUpdate();

        $miu->setInsertAlias("updates");

        $reflection = new \ReflectionClass($miu);

        $alias = $reflection->getProperty("alias");
        $alias->setAccessible(true);

        self::assertEquals("`updates`", $alias->getValue($miu));


        $miu->setInsertAlias("updates", false);
        self::assertEquals("updates", $alias->getValue($miu));
    }

    public function testSetColumnsThrowExceptionOnRowsSet()
    {
        $miu = new MultiInsertUpdate();

        self::expectException(QueryBuilderException::class);
        $miu->addRows([
            ["John", "Doe", "Baker st.", true],
            ["Mohammad", "Abbas", "No 1", false]
        ])->setColumns([
            "name", "family", "address", "isActive"
        ]);
    }

    public function testAddUpdateExceptionWithoutColumns()
    {
        $miu = new MultiInsertUpdate();

        self::expectException(QueryBuilderException::class);
        $miu->addUpdate("name", "John");
    }

    public function testAddUpdateExceptionWithoutRows()
    {
        $miu = new MultiInsertUpdate();

        self::expectException(QueryBuilderException::class);
        $miu->setColumns([
            "name", "family", "address", "isActive"
        ])->addUpdate("name", "John");
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function testAddUpdateEscapeBoolNULL()
    {
        $miu = new MultiInsertUpdate();

        $miu->setColumns([
            "name", "address", "isActive"
        ])->addRows([
            ["John", "USA", false],
            ["Jessi", "CANADA", true],
        ])->addUpdates([
            "isActive" => true,
            "address" => null
        ], false, false);

        $reflection = new \ReflectionClass($miu);

        $updates = $reflection->getProperty("updates");
        $updates->setAccessible(true);

        self::assertEquals("1", $updates->getValue($miu)["isActive"]);
        self::assertEquals("NULL", $updates->getValue($miu)["address"]);
    }

    public function testCompileWithEmptyTable()
    {
        $miu = new MultiInsertUpdate();

        self::expectException(QueryBuilderException::class);
        $miu->setInsertAlias("u")->setColumns([
            "name", "address", "isActive"
        ])->addRows([
            ["John", "USA", false],
            ["Jessi", "CANADA", true],
        ])->addUpdates([
            "isActive" => true,
            "address" => null
        ], false, false)
            ->compile();
    }

    public function testCompileWithNoAlias()
    {
        $miu = new MultiInsertUpdate();

        self::expectException(QueryBuilderException::class);
        $miu->into("Users")->setColumns([
            "name", "address", "isActive"
        ])->addRows([
            ["John", "USA", false],
            ["Jessi", "CANADA", true],
        ])->addUpdates([
            "isActive" => true,
            "address" => null
        ], false, false)
            ->compile();
    }

    public function testCompileWithNoColumn()
    {
        $miu = new MultiInsertUpdate();

        self::expectException(QueryBuilderException::class);
        $miu->into("Users")->setInsertAlias("u")->addRows([
            ["John", "USA", false],
            ["Jessi", "CANADA", true],
        ])->addUpdates([
            "isActive" => true,
            "address" => null
        ], false, false)
            ->compile();
    }

    public function testCompileWithNoRows()
    {
        $miu = new MultiInsertUpdate();

        self::expectException(QueryBuilderException::class);
        $miu->into("Users")->setInsertAlias("u")->setColumns([
            "name", "address", "isActive"
        ])->addUpdates([
            "isActive" => true,
            "address" => null
        ], false, false)
            ->compile();
    }

    public function testCompileWithNoUpdates()
    {
        $miu = new MultiInsertUpdate();

        self::expectException(QueryBuilderException::class);
        $miu->into("Users")->setInsertAlias("u")->setColumns([
            "name", "address", "isActive"
        ])->addRows([
            ["John", "USA", false],
            ["Jessi", "CANADA", true],
        ])->compile();
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function testValidMultiInsertUpdateQuery()
    {
        $miu = new MultiInsertUpdate();
        $query = $miu->into("Users")
            ->setInsertAlias("u")
            ->setColumns([
                "name", "address", "isActive"
            ])
            ->addRows([
                ["John", "USA", false],
                ["Jessi", "CANADA", true],
            ])
            ->addUpdates([
                "isActive" => true,
                "address" => null
            ], false, false)
            ->compile()
            ->getQueryAsString();

        // TODO: is this valid?
        self::assertEquals(
            "INSERT INTO `Users` (`name`,`address`,`isActive`) VALUES ('John','USA',0),('Jessi','CANADA',1) AS `u` ON DUPLICATE KEY UPDATE isActive = 1, address = NULL",
            $query
        );
    }
}
