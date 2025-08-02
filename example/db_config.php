<?php

use Dotenv\Dotenv;
use React\EventLoop\Loop;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Facades\Database;

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



Database::initConnection(
    new DBFactory(
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
    )
);