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

// DELETE FROM Customers WHERE CustomerName= 'Alfreds Futterkiste';
$dbFactory->getQueryBuilder()
    ->delete()
    ->from("Customers")
    ->where("CustomerName", "Alfreds Futterkiste")
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: DELETE FROM Customers WHERE CustomerName= 'Alfreds Futterkiste'" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });

// DELETE FROM Customers WHERE id >= 10 LIMIT 3
$dbFactory->getQueryBuilder()
    ->delete()
    ->from("Customers")
    ->whereGreater("id", 10, true)
    ->setLimit(3)
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: DELETE FROM Customers WHERE id >= 10 LIMIT 3" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });
