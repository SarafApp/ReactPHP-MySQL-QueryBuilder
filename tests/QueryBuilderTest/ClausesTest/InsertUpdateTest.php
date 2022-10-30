<?php

namespace QueryBuilderTest\ClausesTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Clauses\InsertUpdate;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class InsertUpdateTest extends TestCase
{
    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function testSetColumnsThrowExceptionWithRows()
    {
        $insertUpdate = new InsertUpdate();


        self::expectException(QueryBuilderException::class);

        $insertUpdate->setRow([
            "John", "Doe", "Baker St."
        ])->setColumns([
            "name", "family", "address"
        ]);
    }

    public function testSetColumnsResetException()
    {
        $insertUpdate = new InsertUpdate();


        self::expectException(QueryBuilderException::class);

        $insertUpdate->setColumns([
            "name", "family", "address"
        ])->setColumns([
            "name", "family", "address"
        ]);
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function testSetColumnsEscapes()
    {
        $insertUpdate = new InsertUpdate();


        $insertUpdate->setColumns([
            "name", "family", "address"
        ]);

        $reflection = new \ReflectionClass($insertUpdate);

        $columns = $reflection->getProperty("columns");
        $columns->setAccessible(true);

        self::assertCount(3, $columns->getValue($insertUpdate));
        self::assertEquals("`name`", $columns->getValue($insertUpdate)[0]);
        self::assertEquals("`address`", $columns->getValue($insertUpdate)[2]);
    }

    public function testSetRowThrowExceptionOnEmptyColumn()
    {
        $insertUpdate = new InsertUpdate();


        self::expectException(QueryBuilderException::class);

        $insertUpdate->setRow([
            "John", "Doe", "Baker st."
        ]);
    }

    public function testSetRowCountAndColumnCountNotMatch()
    {
        $insertUpdate = new InsertUpdate();


        self::expectException(QueryBuilderException::class);

        $insertUpdate->setColumns([
            "name", "family", "address", "data"
        ])->setRow([
            "John", "Doe", "Baker st."
        ]);
    }


    public function testSetUpdateWithoutAnyRowOrColumns()
    {
        $insertUpdate = new InsertUpdate();


        self::expectException(QueryBuilderException::class);

        $insertUpdate->setUpdate("name", "John");
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function testValidSetUpdate()
    {
        $insertUpdate = new InsertUpdate();


        $insertUpdate->setColumns([
            "name", "family", "isActive"
        ])->setRow([
            "John", "Doe", true
        ])->setUpdates(["isActive" => false]);

        $reflection = new \ReflectionClass($insertUpdate);

        $updates = $reflection->getProperty("updates");
        $updates->setAccessible(true);


        self::assertCount(1, $updates->getValue($insertUpdate));
        self::assertArrayHasKey("`isActive`", $updates->getValue($insertUpdate));
        self::assertEquals("0", $updates->getValue($insertUpdate)['`isActive`']);

        $row = $reflection->getProperty("row");
        $row->setAccessible(true);

        self::assertCount(3, $row->getValue($insertUpdate));
        self::assertEquals("'John'", $row->getValue($insertUpdate)[0]);
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function testValidInsertUpdateQuery()
    {
        $insertUpdate = new InsertUpdate();


        $query = $insertUpdate->into("Users")->setColumns([
            "name", "family", "isActive"
        ])->setRow([
            "John", "Doe", true
        ])->setUpdates(["isActive" => false])->compile()->getQuery();

        self::assertEquals(
            "INSERT INTO `Users` (`name`,`family`,`isActive`) VALUES ('John','Doe',1) ON DUPLICATE KEY UPDATE `isActive` = 0",
            $query
        );
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function testCompileThrowExceptionOnEmptyTable()
    {
        $insertUpdate = new InsertUpdate();


        self::expectException(QueryBuilderException::class);
        $insertUpdate->setColumns([
            "name", "family", "isActive"
        ])->setRow([
            "John", "Doe", true
        ])->setUpdates(["isActive" => false])->compile()->getQuery();
    }
}
