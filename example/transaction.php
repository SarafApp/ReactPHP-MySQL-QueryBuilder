<?php

use Dotenv\Dotenv;
use React\EventLoop\Loop;
use Saraf\QB\QueryBuilder\Contracts\QueryResultCollectionContract;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;
use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResult;

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

$tr = $dbFactory->getQueryBuilder()->beginTransaction();
$tr->addQuery(
    'selectUser',
    $dbFactory->getQueryBuilder()
        ->select()
        ->from("Users")
        ->where('id', 1),
    function (QueryResult $result) {
        if ($result->count == 0) {
            return false;
        }
        return true;
    }
);
$tr->addQuery('selectBalance',
    $dbFactory->getQueryBuilder()
        ->select()
        ->from('Balances')
        ->where('userId', 1)
        ->where('symbol', 'IRT')
);

$tr->addQuery('insertTransactions',
    $dbFactory->getQueryBuilder()
        ->insert()
        ->into('Transactions')
        ->setColumns([
            'userId',
            'type',
            'amount'
        ])
        ->addRow([
            1,
            'WITHDRAW',
            100_000
        ]),
    function (QueryResult $result, QueryResultCollectionContract $contract) {
        if ($contract->get('selectBalance')->rows[0]['balance'] < 100_000) {
            return false;
        }

        return true;
    }
);

$tr->addQuery('updateBalance',
    $dbFactory->getQueryBuilder()
        ->update()
        ->table('Balances')
        ->setUpdate('balance', 'balance - ' . 100_000, false)
        ->where('userId', 1)
        ->where('symbol', 'IRT')
);

$tr->compile()->then(function ($result) {
    var_dump($result);
})->catch(function (\Throwable $throwable) {
    var_dump($throwable->getMessage());
});