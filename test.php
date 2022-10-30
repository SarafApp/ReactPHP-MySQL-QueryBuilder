<?php

use Saraf\QB\QueryBuilder\Enums\OrderDirection;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;
use Saraf\QB\QueryBuilder\QueryBuilder;

include "vendor/autoload.php";

$queryBuilder = new QueryBuilder();



try {
    // Select
    echo $queryBuilder
        ->select()
        ->from("Users")
        ->addColumn("username")
        ->addColumn("password")
        ->addColumnAsAlias("TIME()", "time", false)
        ->addColumnMin("id", "minId")
        ->where("ali", "reza")
        ->whereLike("time", "TIME()", true, true, false)
        ->whereLike("username", "u_", true, false)
        ->or()
        ->whereLike("username", "u_", true, false)
        ->groupBy("userRole")
        ->addOrder("id", OrderDirection::Ascending)
        ->addOrder("userId", OrderDirection::Descending)
        ->addRandomOrder()
        ->setLimit(10)
        ->setOffset(2)
        ->compile()
        ->getQuery();

    echo PHP_EOL;

    // Select With Left Join
    echo $queryBuilder
        ->select()
        ->addColumnAsAlias("Trades.id", "tradeId")
        ->addColumnAsAlias("Trades.amount", "tradeAmount")
        ->from("Trades")
        ->leftJoin("Users", "Trades.user", 'Users.id')
        ->addOrder("id", OrderDirection::Ascending)
        ->setOffset(29)
        ->setLimit(10)
        ->compile()
        ->getQuery();

    echo PHP_EOL;

    // Multi Insert Update
    echo $queryBuilder
        ->multiInsertUpdate()
        ->into("Users")
        ->setColumns(['id', 'name', 'password'])
        ->addRow([1, 'parsa', '1309123213'])
        ->addRow([2, 'ali', '1309123213'])
        ->addRows([
            [3, 'mamad', '1309123213'],
            [4, 'hassan', '1309123213']
        ])
        ->setInsertAlias("newData")
        ->addUpdate("name", "unknown")
        ->addUpdate("password", "newData.password", false)
        ->addUpdates([
            "password" => "newData.password",
            "name" => "newData.name"
        ], false)
        ->compile()
        ->getQuery();

    echo PHP_EOL;

    // Delete
    echo $queryBuilder
        ->delete()
        ->from("Uses")
        ->or()
        ->whereGreater("id", 20)
        ->or()
        ->whereGreater("id", 20)
        ->or()
        ->whereGreater("id", 20)
        ->whereGreater("id", 20)
        ->whereGreater("id", 20)
        ->setLimit(20)
        ->setOffset(2)
        ->compile()
        ->getQuery();

    echo PHP_EOL;

    // Insert
    echo $queryBuilder
        ->insert()
        ->into("Users")
        ->setColumns(['id', 'name', 'password'])
        ->addRow([1, 'parsa', '1309123213'])
        ->addRow([2, 'ali', '1309123213'])
        ->addRows([
            [3, 'mamad', '1309123213'],
            [4, 'hassan', '1309123213']
        ])
        ->compile()
        ->getQuery();

    echo PHP_EOL;

    // Update
    echo $queryBuilder
        ->update()
        ->table("Users")
        ->setUpdate("username", "reza")
        ->setUpdates(['isActive' => true, 'isSend' => false])
        ->where("id", 2)
        ->whereBetween("registerTime", "TIME()-10000", "TIME()-20000", false)
        ->or()
        ->where("id", 3)
        ->whereIsNotNull("description")
        ->compile()
        ->getQuery();

    echo PHP_EOL;

    // Insert and Update
    echo $queryBuilder
        ->insertUpdate()
        ->into("Users")
        ->setColumns(["id", "name", "password"])
        ->setRow([1, 'alireza', '1234'])
        ->setUpdate("sname", "reza", false)
        ->setUpdates(['password' => '1234', 'name' => "d"])
        ->compile()
        ->getQuery();

    echo PHP_EOL;

    // Multi Insert Update
    echo $queryBuilder
        ->multiInsertUpdate()
        ->into("Users")
        ->setColumns(['id', 'name', 'password'])
        ->addRow([1, 'parsa', '1309123213'])
        ->addRow([2, 'ali', '1309123213'])
        ->addRows([
            [3, 'mamad', '1309123213'],
            [4, 'hassan', '1309123213']
        ])
        ->setInsertAlias("newData")
        ->addUpdate("name", "unknown")
        ->addUpdate("password", "newData.password", false)
        ->addUpdates([
            "password" => "newData.password",
            "sname" => "newData.name"
        ], false)
        ->compile()
        ->getQuery();

} catch (QueryBuilderException $e) {
    echo $e->getTraceAsString();
}

