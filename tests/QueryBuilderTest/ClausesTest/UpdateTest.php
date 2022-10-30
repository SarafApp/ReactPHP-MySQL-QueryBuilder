<?php

namespace QueryBuilderTest\ClausesTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Clauses\Update;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class UpdateTest extends TestCase
{
    /**
     * @test
     */
    public function setUpdatesWithEscape()
    {
        $u = new Update();


        $u->setUpdate("name", "John", true)
            ->setUpdate("family", "Doe", false);

        $reflection = new \ReflectionClass($u);

        $updates = $reflection->getProperty("updates");
        $updates->setAccessible(true);

        self::assertCount(2, $updates->getValue($u));
        self::assertEquals("`name` = 'John'", $updates->getValue($u)[0]);
        self::assertEquals("`family` = Doe", $updates->getValue($u)[1]);
    }

    /**
     * @test
     */
    public function setUpdatesTest()
    {
        $u = new Update();

        $u->setUpdates([
            "name" => "John",
            "family" => "Doe"
        ]);

        $reflection = new \ReflectionClass($u);

        $updates = $reflection->getProperty("updates");
        $updates->setAccessible(true);

        self::assertCount(2, $updates->getValue($u));
        self::assertEquals("`name` = 'John'", $updates->getValue($u)[0]);
        self::assertEquals("`family` = 'Doe'", $updates->getValue($u)[1]);
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function updateQueryWithoutWhere()
    {
        $u = new Update();

        self::expectException(QueryBuilderException::class);
        $u->table("Users")->setUpdates([
            "name" => "John",
            "family" => "Doe"
        ])->compile();
    }

    /**
     * @test
     */
    public function updateQueryWithNoTableSpecified()
    {
        $u = new Update();

        self::expectException(QueryBuilderException::class);
        $u->setUpdates([
            "name" => "John",
            "family" => "Doe"
        ])->compile();
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function updateQueryWithoutUpdates()
    {
        $u = new Update();

        self::expectException(QueryBuilderException::class);
        $u->table("Users")->where("id", 1)->compile();
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function aValidUpdateQuery()
    {
        $u = new Update();

        $query = $u->table("Users")->setUpdates([
            "name" => "John",
            "family" => "Doe"
        ])->where("id", 1)->compile()->getQuery();

        self::assertEquals(
            "UPDATE `Users` SET `name` = 'John', `family` = 'Doe' WHERE (`id` = 1)",
            $query
        );
    }
}
