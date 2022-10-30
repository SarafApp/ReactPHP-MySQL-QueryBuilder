<?php

use Dotenv\Dotenv;
use React\EventLoop\Loop;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Enums\OrderDirection;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;

include "vendor/autoload.php";

// Loop
$loop = Loop::get();

// Environments
$env = Dotenv::createImmutable(__DIR__ . "/../");
$env->load();

// Env Loader
$DB_NAME = $_ENV['DB_NAME'];
$DB_USER = $_ENV['DB_USER'];
$DB_PASS = $_ENV['DB_PASS'];
$DB_HOST = $_ENV['DB_HOST'];
$DB_PORT_READ = $_ENV['DB_PORT_READ'];
$DB_PORT_WRITE = $_ENV['DB_PORT_WRITE'];


try {
    $dbFactory = new DBFactory(
        $loop,
        $DB_HOST,
        $DB_NAME,
        $DB_USER,
        $DB_PASS,
        $DB_PORT_WRITE,
        $DB_PORT_READ,
        5,
        5,
        2,
        2
    );
} catch (DBFactoryException $e) {
    echo $e->getMessage();
    exit(1);
}


$loop->run();

// INSERT INTO Users (id, name, age) VALUES (10, "Roy", 34),(11, "Ali", 30),(12,"Mahdi",20) as alias ON DUPLICATE KEY UPDATE name=alias.name, age=alias.age
$dbFactory->getQueryBuilder()
    ->multiInsertUpdate()
    ->into("Users")
    ->setColumns(["id", "name", "age"])
    ->addRows([
        [10, 'Ray', 34],
        [11, 'Ali', 30],
        [12, 'Mahdi', 20]
    ])
    ->setInsertAlias("alias")
    ->addUpdate("name", "alias.name", false)
    ->addUpdate("age", "alias.age", false)
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: INSERT INTO Users (id, name, age) VALUES (10, \"Roy\", 34),(11, \"Ali\", 30),(12,\"Mahdi\",20) as alias ON DUPLICATE KEY UPDATE name=alias.name, age=alias.age" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });
