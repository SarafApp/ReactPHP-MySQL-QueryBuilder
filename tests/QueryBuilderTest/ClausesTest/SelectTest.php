<?php

namespace QueryBuilderTest\ClausesTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Clauses\Select;
use Saraf\QB\QueryBuilder\Enums\OrderDirection;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class SelectTest extends TestCase
{
    /**
     * @test
     */
    public function setDistinctSets()
    {
        $s = new Select();

        $s->setDistinct();

        $reflection = new \ReflectionClass($s);

        $isDistinct = $reflection->getProperty("isDistinct");
        $isDistinct->setAccessible(true);
        self::assertTrue($isDistinct->getValue($s));
    }

    /**
     * @test
     */
    public function setDistinctDefaultFalse()
    {
        $s = new Select();

        $reflection = new \ReflectionClass($s);

        $isDistinct = $reflection->getProperty("isDistinct");
        $isDistinct->setAccessible(true);
        self::assertFalse($isDistinct->getValue($s));
    }

    /**
     * @test
     */
    public function addColumnEscapesWorks()
    {
        $s = new Select();

        $s->addColumn("username", true);
        $s->addColumn("COUNT(*)", false);
        $s->addColumn("Trades.id", true);


        $reflection = new \ReflectionClass($s);

        $statements = $reflection->getProperty("statements");
        $statements->setAccessible(true);

        self::assertCount(3, $statements->getValue($s));

        self::assertEquals("`username`", $statements->getValue($s)[0]);
        self::assertEquals("COUNT(*)", $statements->getValue($s)[1]);
        self::assertEquals("`Trades`.`id`", $statements->getValue($s)[2]);
    }

    /**
     * @test
     */
    public function addColumnsEscapesWorks()
    {
        $s = new Select();

        $s->addColumns(["username", "Trades.id"], true);
        $s->addColumn("COUNT(*)", false);
        $s->addColumns(["id", "name", "family"]);


        $reflection = new \ReflectionClass($s);

        $statements = $reflection->getProperty("statements");
        $statements->setAccessible(true);

        self::assertCount(6, $statements->getValue($s));

        self::assertEquals("`username`", $statements->getValue($s)[0]);
        self::assertEquals("`Trades`.`id`", $statements->getValue($s)[1]);
        self::assertEquals("`family`", $statements->getValue($s)[5]);
    }

    /**
     * @test
     */
    public function addAllColumnsThrowException()
    {
        $s = new Select();

        $s->addColumns(["id", "name", "family"]);

        self::expectException(QueryBuilderException::class);
        $s->addAllColumns();
    }

    /**
     * @test
     */
    public function addAllColumns()
    {
        $s = new Select();

        $s->addAllColumns();

        $reflection = new \ReflectionClass($s);

        $statements = $reflection->getProperty("statements");
        $statements->setAccessible(true);

        self::assertCount(1, $statements->getValue($s));
        self::assertEquals("*", $statements->getValue($s)[0]);
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function addColumnSum()
    {
        $s = new Select();

        $s->addColumnSum("price", "p");

        $reflection = new \ReflectionClass($s);

        $statements = $reflection->getProperty("statements");
        $statements->setAccessible(true);

        self::assertCount(1, $statements->getValue($s));
        self::assertEquals("SUM(`price`) AS `p`", $statements->getValue($s)[0]);
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function addCountWithDefault()
    {
        $s = new Select();

        $s->addColumnCount();

        $reflection = new \ReflectionClass($s);

        $statements = $reflection->getProperty("statements");
        $statements->setAccessible(true);

        self::assertCount(1, $statements->getValue($s));
        self::assertEquals("COUNT(*)", $statements->getValue($s)[0]);
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function addCountWithAColumn()
    {
        $s = new Select();

        $s->addColumnCount("id");

        $reflection = new \ReflectionClass($s);

        $statements = $reflection->getProperty("statements");
        $statements->setAccessible(true);

        self::assertCount(1, $statements->getValue($s));
        self::assertEquals("COUNT(`id`)", $statements->getValue($s)[0]);
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function compileThrowExceptionIfTableIsEmpty()
    {
        $s = new Select();

        $s->addColumn("Users.trade")
            ->addColumns(["id", "name", "family"]);

        self::expectException(QueryBuilderException::class);
        $s->compile();
    }

    public function testAddColumnMin()
    {
        $s = new Select();

        $s->addColumnMin("price");

        $s->addColumnMin("amount", "least", false);

        $reflection = new \ReflectionClass($s);

        $statements = $reflection->getProperty("statements");
        $statements->setAccessible(true);

        self::assertCount(2, $statements->getValue($s));
        self::assertEquals("MIN(`price`)", $statements->getValue($s)[0]);
        self::assertEquals("MIN(amount) AS `least`", $statements->getValue($s)[1]);
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function testAddColumnMax()
    {
        $s = new Select();

        $s->addColumnMax("price");

        $s->addColumnMax("amount", "best", false);

        $reflection = new \ReflectionClass($s);

        $statements = $reflection->getProperty("statements");
        $statements->setAccessible(true);

        self::assertCount(2, $statements->getValue($s));
        self::assertEquals("MAX(`price`)", $statements->getValue($s)[0]);
        self::assertEquals("MAX(amount) AS `best`", $statements->getValue($s)[1]);
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function testAddColumnAVG()
    {
        $s = new Select();

        $s->addColumnAverage("price");

        $s->addColumnAverage("amount", "medium", false);

        $reflection = new \ReflectionClass($s);

        $statements = $reflection->getProperty("statements");
        $statements->setAccessible(true);

        self::assertCount(2, $statements->getValue($s));
        self::assertEquals("AVG(`price`)", $statements->getValue($s)[0]);
        self::assertEquals("AVG(amount) AS `medium`", $statements->getValue($s)[1]);
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function queryConstructValidWhenUseEverythingTogether()
    {
        $s = new Select();

        // it should throw exception because no statements added
        $q = $s->from("Users")
            ->addColumns([
                "id",
                "name",
                "family",
                "address"
            ])
            ->addColumn("COUNT(*)", false)
            ->setDistinct()
            ->whereGroup([
                "name" => "John",
                "family" => "Doe"
            ])
            ->or()
            ->whereGroup([
                "name" => "Mohammad",
                "family" => "Mohammadi"
            ])
            ->setLimit(20)
            ->setOffset(20)
            ->whereIn("status", ["APPROVED", "PENDING"])
            ->leftJoin("Trades", "Trades.user", "Users.id")
            ->addRandomOrder()
            ->groupBy("roles")
            ->addOrder("family", OrderDirection::Descending)
            ->compile();

        self::assertEquals(
            "SELECT DISTINCT `id`,`name`,`family`,`address`,COUNT(*) FROM `Users` LEFT JOIN `Trades` ON `Trades`.`user` = `Users`.`id` WHERE (`name` = 'John' AND `family` = 'Doe') OR (`name` = 'Mohammad' AND `family` = 'Mohammadi' AND `status` IN ('APPROVED','PENDING')) GROUP BY `roles` ORDER BY RAND(), `family` DESC LIMIT 20 OFFSET 20",
            $q->getQuery()
        );
    }
}
