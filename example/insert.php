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

// INSERT INTO Customers (CustomerName, ContactName, Address) VALUES ('Cardinal', 'Tom B. Erichsen', 'Skagen 21');
$dbFactory->getQueryBuilder()
    ->insert()
    ->into("Customers")
    ->setColumns(["CustomerName", "ContactName", "Address"])
    ->addRow(['Cardinal', 'Tom B. Erichsen', 'Skagen 21'])
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: INSERT INTO Customers (CustomerName, ContactName, Address) VALUES ('Cardinal', 'Tom B. Erichsen', 'Skagen 21')" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });


// INSERT INTO Customers (CustomerName, ContactName, Address) VALUES ('Cardinal', 'Tom B. Erichsen', 'Skagen 21'),('MamadReza', 'Ola Sr', 'Samoel');
$dbFactory->getQueryBuilder()
    ->insert()
    ->into("Customers")
    ->setColumns(["CustomerName", "ContactName", "Address"])
    ->addRows([
        ['Cardinal', 'Tom B. Erichsen', 'Skagen 21'],
        ['MamadReza', 'Ola Sr', 'Samoel'],
    ])
    ->compile()
    ->getQuery()
    ->then(function ($result) {
        echo "Excepted: INSERT INTO Customers (CustomerName, ContactName, Address) VALUES ('Cardinal', 'Tom B. Erichsen', 'Skagen 21'),('MamadReza', 'Ola Sr', 'Samoel')" . PHP_EOL;
        echo "Actual:   " . $result['query'] . PHP_EOL . PHP_EOL;
    });
