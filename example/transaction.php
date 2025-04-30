<?php

use Dotenv\Dotenv;
use React\EventLoop\Loop;
use Saraf\QB\QueryBuilder\Contracts\Transaction\TransactionQueryContract;
use Saraf\QB\QueryBuilder\Core\DBFactory;
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

$dbFactory->getQueryBuilder()
    ->transaction(function (TransactionQueryContract $query) use ($dbFactory) {

        return \React\Promise\all([
            $dbFactory->getQueryBuilder()
                ->insert()
                ->into("users")
                ->setColumns(["name", "email"])
                ->addRow(['Albert', 'albert@gmail.com'])
                ->compile()
                ->commit()
                ->then(function ($res) use ($query) {

                    if ($res['result'] !== true) {

                        $query->rollback($res['error']);
                    }
                }),

            $dbFactory->getQueryBuilder()
                ->insert()
                ->into("users")
                ->setColumns(["name", "email"])
                ->addRow(['Mana', 'mana@gmail.com'])
                ->compile()
                ->commit()
                ->then(function ($res) use ($query) {

                    if ($res['result'] !== true) {

                        $query->rollback($res['error']);
                    }
                })
        ]);
    });


$dbFactory->getQueryBuilder()
    ->transaction(function (TransactionQueryContract $query) use ($dbFactory) {

        return new \React\Promise\Promise(function ($resolve, $reject) use ($dbFactory, $query) {
            $resolve($dbFactory->getQueryBuilder()
                ->insert()
                ->into("users")
                ->setColumns(["name", "email"])
                ->addRow(['Yank', 'yank@gmail.com'])
                ->compile()
                ->commit()
                ->then(function ($res) use ($query) {

                    if ($res['result'] !== true) {

                        $query->rollback($res['error']);
                    }
                }));
        });
    });


$dbFactory->getQueryBuilder()
    ->transaction(function (TransactionQueryContract $query) use ($dbFactory) {

        return $dbFactory->getQueryBuilder()
            ->insert()
            ->into("users")
            ->setColumns(["name", "email"])
            ->addRow(['Ban', 'ban@gmail.com'])
            ->compile()
            ->commit()
            ->then(function ($res) use ($query) {

                if ($res['result'] !== true) {

                    $query->rollback($res['error']);
                }
            });
    });
