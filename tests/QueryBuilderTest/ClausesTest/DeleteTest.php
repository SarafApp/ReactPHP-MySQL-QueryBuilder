<?php

namespace QueryBuilderTest\ClausesTest;

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Clauses\Delete;
use Saraf\QB\QueryBuilder\Core\Query;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

final class DeleteTest extends TestCase
{
    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function compileThrowExceptionForFromTable()
    {
        $d = new Delete();

        self::expectException(QueryBuilderException::class);
        $d->where("id", 1)->setLimit(1)->compile();
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function compileThrowExceptionWithoutWhere()
    {
        $d = new Delete();

        self::expectException(QueryBuilderException::class);
        $d->from("Users")->setLimit(1)->compile();
    }

    /**
     * @test
     * @throws \Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException
     */
    public function compileTest()
    {
        $d = new Delete();

        $query = $d->from("Users")
            ->where("id", 1)
            ->setLimit(1)
            ->setOffset(1)
            ->compile();

        self::assertInstanceOf(Query::class, $query);

        self::assertEquals(
            "DELETE FROM `Users` WHERE (`id` = 1) LIMIT 1 OFFSET 1",
            $query->getQuery()
        );
    }
}
