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

// Without QB
$dbFactory->getQueryBuilder()
    ->select()
    ->from("Users")
    ->addColumn("id")
    ->whereGreater("id", 1)
    ->compile()
    ->stream()
    ->onData(function ($result) {
        echo "New Row Data:" . json_encode($result) . PHP_EOL;
    })
    ->run();

// Without QueryBuilder
$dbFactory->streamQuery("select id from Users where id > 1")
    ->onError(function (Exception $result) {
        echo "Error " . $result->getMessage() . PHP_EOL;
    })
    ->onData(function ($result) {
        echo "New Row Data:" . json_encode($result) . PHP_EOL;
    })
    ->onClosed(function () {
        echo "Task Finished";
    })
    ->run();

$loop->addPeriodicTimer(1, function () {
    memory();
});

function memory()
{
    echo "Memory Stat: " . round(memory_get_usage() / 1_000_000, 2) . " / " . round(memory_get_usage(true) / 1_000_000, 2) . " MB" .
        " | Peak: " . round(memory_get_peak_usage() / 1_000_000, 2) . " / " . round(memory_get_peak_usage(true) / 1_000_000, 2) . " MB" . PHP_EOL;
}


$loop->run();
