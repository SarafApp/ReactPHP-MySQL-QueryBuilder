<?php

use Dotenv\Dotenv;
use React\EventLoop\Loop;
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
        1,
        1,
        2,
        2,
        'utf8mb4',
        false,
        // maxWriteConnections — pool can grow up to this many on demand
        4
    );
} catch (DBFactoryException $e) {
    echo $e->getMessage();
    exit(1);
}

$qb = $dbFactory->getQueryBuilder();

// 1) Happy path: INSERT + UPDATE in one transaction
$qb->transaction(function ($tx) {
    return $tx->insert()
        ->into("Customers")
        ->setColumns(["CustomerName", "ContactName", "Address"])
        ->addRow(['TxCardinal', 'Tom B. Erichsen', 'Skagen 21'])
        ->compile()
        ->commit()
        ->then(function ($insertResult) use ($tx) {
            if (!$insertResult['result']) {
                throw new \RuntimeException("insert failed: " . $insertResult['error']);
            }
            return $tx->update()
                ->table("Customers")
                ->setUpdates(['ContactName' => 'Renamed'])
                ->where("CustomerName", "TxCardinal")
                ->compile()
                ->commit()
                ->then(fn($r) => ['insertId' => $insertResult['insertId'] ?? null, 'affected' => $r['affectedRows'] ?? 0]);
        });
})->then(function ($outer) {
    echo "case 1 (happy path): " . json_encode($outer) . PHP_EOL;
});

// 2) Inner failure: closure returns result=>false → auto ROLLBACK
$qb->transaction(function ($tx) {
    return $tx->insert()
        ->into("Customers")
        ->setColumns(["CustomerName"])
        ->addRow(['SomeName'])
        ->compile()
        ->commit()
        ->then(fn() => ['result' => false, 'error' => 'simulated business rule violation']);
})->then(function ($outer) {
    echo "case 2 (inner failure): " . json_encode($outer) . PHP_EOL;
});

// 3) Closure throws → ROLLBACK
$qb->transaction(function ($tx) {
    throw new \RuntimeException("synchronous failure");
})->then(function ($outer) {
    echo "case 3 (throw): " . json_encode($outer) . PHP_EOL;
});

// 4) Raw escape hatch — run any SQL on the reserved connection
$qb->transaction(function ($tx) {
    return $tx->raw("SELECT @@hostname")
        ->then(fn($r) => ['hostname' => $r['rows'][0] ?? null]);
})->then(function ($outer) {
    echo "case 4 (raw): " . json_encode($outer) . PHP_EOL;
});

$loop->run();
