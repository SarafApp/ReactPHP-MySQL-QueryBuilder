<?php

namespace QueryBuilderTest\CoreTest;

use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use function React\Promise\all;
use function React\Promise\reject;

/**
 * Integration test for concurrent transactions against a real MySQL server.
 *
 * Skipped unless every DB_* env var below is set. Run with e.g.:
 *
 *   DB_HOST=127.0.0.1 DB_NAME=test DB_USER=root DB_PASS=secret \
 *   DB_PORT_WRITE=3306 DB_PORT_READ=3306 \
 *   ./vendor/bin/phpunit tests/QueryBuilderTest/CoreTest/TransactionIntegrationTest.php
 *
 * The test deliberately targets a non-existent table so it leaves no schema
 * footprint — every transaction is expected to roll back at the INSERT step.
 */
final class TransactionIntegrationTest extends TestCase
{
    private const REQUIRED_ENV = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT_WRITE', 'DB_PORT_READ'];

    private static function env(string $k): ?string
    {
        if (isset($_ENV[$k]) && $_ENV[$k] !== '') return (string) $_ENV[$k];
        $v = getenv($k);
        return ($v === false || $v === '') ? null : $v;
    }

    private static function loadDotEnvIfPresent(): void
    {
        $root = dirname(__DIR__, 3);
        $envFile = $root . '/.env';
        if (!is_file($envFile)) return;
        if (!class_exists(\Dotenv\Dotenv::class)) return;

        \Dotenv\Dotenv::createImmutable($root)->safeLoad();
    }

    protected function setUp(): void
    {
        self::loadDotEnvIfPresent();
        foreach (self::REQUIRED_ENV as $k) {
            if (self::env($k) === null) {
                self::markTestSkipped("DB env var '$k' not set; integration test skipped");
            }
        }
    }

    /** @test */
    public function concurrentTransactionsReserveIsolatedConnectionsAndRespectCap()
    {
        $loop = Loop::get();
        $factory = new DBFactory(
            loop: $loop,
            host: self::env('DB_HOST'),
            dbName: self::env('DB_NAME'),
            username: self::env('DB_USER'),
            password: self::env('DB_PASS'),
            writePort: (int) self::env('DB_PORT_WRITE'),
            readPort: (int) self::env('DB_PORT_READ'),
            writeInstanceCount: 1,
            readInstanceCount: 1,
            timeout: 5,
            idle: 30,
            charset: 'utf8mb4',
            debugMode: true,
            maxWriteConnections: 3
        );

        $qb = $factory->getQueryBuilder();

        $capturedCids = [];
        $outcomes = [];
        $nonexistentTable = "__qb_nonexistent_" . bin2hex(random_bytes(4));

        $launch = function (int $i) use ($qb, &$capturedCids, &$outcomes, $nonexistentTable) {
            return $qb->transaction(function ($tx) use ($i, &$capturedCids, $nonexistentTable) {
                return $tx->raw("SELECT CONNECTION_ID() AS cid")
                    ->then(function ($r) use ($tx, $i, &$capturedCids, $nonexistentTable) {
                        $capturedCids[$i] = $r['rows'][0]['cid'] ?? null;
                        return $tx->insert()
                            ->into($nonexistentTable)
                            ->setColumns(["x"])
                            ->addRow([1])
                            ->compile()
                            ->commit();
                    });
            })->then(function ($outer) use ($i, &$outcomes) {
                $outcomes[$i] = $outer;
            });
        };

        // Fire 4 in the same tick: cap=3, so the 4th must fail at 'reserve'
        // synchronously without invoking its closure.
        $all = all([$launch(0), $launch(1), $launch(2), $launch(3)]);

        $timeoutTimer = $loop->addTimer(15, function () use ($loop) {
            $loop->stop();
            self::fail("integration test timed out (15s)");
        });

        $all->then(function () use ($loop, $timeoutTimer) {
            $loop->cancelTimer($timeoutTimer);
            $loop->stop();
        });

        $loop->run();

        // Partition outcomes by failedAt
        $reserves = array_filter($outcomes, fn($o) => ($o['failedAt'] ?? null) === 'reserve');
        $inners = array_filter($outcomes, fn($o) => ($o['failedAt'] ?? null) === 'inner');

        self::assertCount(4, $outcomes, "All four transactions settled");
        self::assertCount(1, $reserves, "Exactly one tx must hit the cap (failedAt=reserve)");
        self::assertCount(3, $inners, "The other three must roll back at the INSERT step (failedAt=inner)");

        // The tx that hit the cap must NOT have captured a CID — its closure never ran.
        $reservedIndex = array_key_first($reserves);
        self::assertArrayNotHasKey($reservedIndex, $capturedCids, "Capped tx's closure must not have executed");

        // The three running txs each captured a distinct CONNECTION_ID() — proving
        // they each held an isolated MySQL connection for the transaction duration.
        $cids = array_values(array_filter($capturedCids, fn($v) => $v !== null));
        self::assertCount(3, $cids, "Three concurrent transactions captured a connection id");
        self::assertCount(
            3,
            array_unique($cids),
            "Each concurrent transaction must run on a distinct MySQL connection; got " . json_encode($cids)
        );

        // Pool grew on demand from 1 to the cap.
        $trace = $factory->getTrace();
        self::assertTrue($trace['result']);
        self::assertCount(3, $trace['workers']['write'] ?? [], "Write pool grew on demand to cap=3");

        // After release, a follow-up transaction should reuse one of the existing
        // connections (no further growth) and successfully commit a trivial SELECT.
        $followup = null;
        $qb->transaction(function ($tx) {
            return $tx->raw("SELECT 1 AS one")
                ->then(fn($r) => ['one' => $r['rows'][0]['one'] ?? null]);
        })->then(function ($r) use (&$followup, $loop) {
            $followup = $r;
            $loop->stop();
        });
        $loop->addTimer(5, fn() => $loop->stop());
        $loop->run();

        self::assertNotNull($followup, "Follow-up transaction did not settle");
        self::assertTrue($followup['result'], "Follow-up should commit");
        self::assertSame(1, (int) ($followup['data']['one'] ?? 0));

        $trace2 = $factory->getTrace();
        self::assertCount(3, $trace2['workers']['write'] ?? [], "Pool stayed at 3 — no extra growth after release");
    }

    /**
     * @test
     *
     * End-to-end isolation proof against a real table. We:
     *  1) create a real table with a UNIQUE constraint and one seed row,
     *  2) fire 6 concurrent transactions covering every failure mode and the
     *     reservation cap, each performing visible mutations that would persist
     *     if their tx committed,
     *  3) wait for all to settle,
     *  4) verify the final table state shows ONLY the happy-path mutations
     *     survived (proving every other tx rolled back atomically),
     *  5) clean up the table.
     */
    public function concurrentMixedOutcomesPreserveAtomicityAgainstRealTable()
    {
        $loop = Loop::get();
        $factory = new DBFactory(
            loop: $loop,
            host: self::env('DB_HOST'),
            dbName: self::env('DB_NAME'),
            username: self::env('DB_USER'),
            password: self::env('DB_PASS'),
            writePort: (int) self::env('DB_PORT_WRITE'),
            readPort: (int) self::env('DB_PORT_READ'),
            writeInstanceCount: 1,
            readInstanceCount: 1,
            timeout: 5,
            idle: 30,
            charset: 'utf8mb4',
            debugMode: true,
            maxWriteConnections: 6
        );

        $qb = $factory->getQueryBuilder();
        $table = "__qb_tx_test_" . bin2hex(random_bytes(4));

        try {
            // --- 1) Create table and seed it ---
            $this->runOnLoop($loop, function () use ($factory, $table) {
                return $factory->query(
                    "CREATE TABLE `$table` ("
                    . " id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
                    . " name VARCHAR(64) NOT NULL UNIQUE,"
                    . " counter INT NOT NULL DEFAULT 0"
                    . ") ENGINE=InnoDB"
                );
            });
            $this->runOnLoop($loop, function () use ($factory, $table) {
                return $factory->query("INSERT INTO `$table` (name, counter) VALUES ('seed', 0)");
            });

            // --- 2) Define six concurrent scenarios ---
            $outcomes = [];
            $capturedCids = [];

            $record = function (string $key) use (&$outcomes) {
                return function ($outer) use ($key, &$outcomes) {
                    $outcomes[$key] = $outer;
                };
            };

            $captureCid = function (string $key) use ($qb, &$capturedCids) {
                return function ($tx) use ($key, &$capturedCids) {
                    return $tx->raw("SELECT CONNECTION_ID() AS cid")
                        ->then(function ($r) use ($key, &$capturedCids) {
                            $capturedCids[$key] = $r['rows'][0]['cid'] ?? null;
                            return $r;
                        });
                };
            };

            // (a) Happy path: insert 'happy_name' + increment seed counter; commit.
            $happy = $qb->transaction(function ($tx) use ($table, $captureCid) {
                return $captureCid('happy')($tx)
                    ->then(fn() => $tx->insert()
                        ->into($table)->setColumns(['name', 'counter'])->addRow(['happy_name', 0])
                        ->compile()->commit())
                    ->then(function ($r) use ($tx, $table) {
                        if (!$r['result']) {
                            return ['result' => false, 'error' => 'happy insert failed: ' . $r['error']];
                        }
                        return $tx->update()
                            ->table($table)
                            ->setUpdates(['counter' => 1])
                            ->where("name", "seed")
                            ->compile()->commit();
                    });
            })->then($record('happy'));

            // (b) Duplicate key: insert 'seed' (UNIQUE violation) — result=>false from
            //     MySQL → auto-rollback (failedAt=inner). No row inserted.
            $dup = $qb->transaction(function ($tx) use ($table, $captureCid) {
                return $captureCid('dup')($tx)
                    ->then(fn() => $tx->insert()
                        ->into($table)->setColumns(['name', 'counter'])->addRow(['seed', 99])
                        ->compile()->commit());
            })->then($record('dup'));

            // (c) Throw inside an async .then() AFTER an insert: the insert succeeded
            //     server-side, but the throw in the .then() rejects the chain and triggers
            //     rollback (failedAt=inner). 'async_throw_name' must NOT exist.
            $throw = $qb->transaction(function ($tx) use ($table, $captureCid) {
                return $captureCid('throw')($tx)
                    ->then(fn() => $tx->insert()
                        ->into($table)->setColumns(['name', 'counter'])->addRow(['async_throw_name', 0])
                        ->compile()->commit())
                    ->then(function () {
                        throw new \RuntimeException("simulated post-insert throw");
                    });
            })->then($record('throw'));

            // (c2) Synchronous throw from the closure body BEFORE returning any promise.
            //      This is the only path that produces failedAt=closure. No INSERT is
            //      attempted, so the only side effect is BEGIN + ROLLBACK.
            $syncThrow = $qb->transaction(function ($tx) use ($captureCid) {
                $captureCid('sync_throw')($tx); // fire-and-forget; we never await it
                throw new \RuntimeException("simulated sync closure throw");
            })->then($record('sync_throw'));

            // (d) Promise rejection AFTER an insert. 'reject_name' must NOT exist.
            $rej = $qb->transaction(function ($tx) use ($table, $captureCid) {
                return $captureCid('reject')($tx)
                    ->then(fn() => $tx->insert()
                        ->into($table)->setColumns(['name', 'counter'])->addRow(['reject_name', 0])
                        ->compile()->commit())
                    ->then(fn() => reject(new \RuntimeException("simulated downstream reject")));
            })->then($record('reject'));

            // (e) Manual result=>false AFTER an insert. 'manual_false_name' must NOT exist.
            $manual = $qb->transaction(function ($tx) use ($table, $captureCid) {
                return $captureCid('manual')($tx)
                    ->then(fn() => $tx->insert()
                        ->into($table)->setColumns(['name', 'counter'])->addRow(['manual_false_name', 0])
                        ->compile()->commit())
                    ->then(fn() => ['result' => false, 'error' => 'business rule rejected the row']);
            })->then($record('manual'));

            // (f) 6th tx in the same tick: cap=5 → fails at 'reserve' without invoking closure.
            $cap = $qb->transaction(function ($tx) use ($captureCid) {
                // This must not execute.
                return $captureCid('cap')($tx);
            })->then($record('cap'));

            $all = all([$happy, $dup, $throw, $syncThrow, $rej, $manual, $cap]);

            $timeoutTimer = $loop->addTimer(20, function () use ($loop) {
                $loop->stop();
                self::fail("mixed-outcome integration test timed out (20s)");
            });
            $all->then(function () use ($loop, $timeoutTimer) {
                $loop->cancelTimer($timeoutTimer);
                $loop->stop();
            });
            $loop->run();

            // --- 3) Verify per-tx outcomes ---
            self::assertCount(7, $outcomes, "All seven transactions settled");

            self::assertTrue(
                $outcomes['happy']['result'],
                "Happy path must commit. Outcome dump: " . json_encode($outcomes['happy'])
            );

            self::assertFalse($outcomes['dup']['result']);
            self::assertSame('inner', $outcomes['dup']['failedAt'], "Duplicate key triggers inner-failure rollback");

            self::assertFalse($outcomes['throw']['result']);
            self::assertSame('inner', $outcomes['throw']['failedAt'], "Throw inside .then() becomes a promise rejection (inner)");

            self::assertFalse($outcomes['sync_throw']['result']);
            self::assertSame('closure', $outcomes['sync_throw']['failedAt'], "Synchronous throw from closure body must surface as failedAt=closure");

            self::assertFalse($outcomes['reject']['result']);
            self::assertSame('inner', $outcomes['reject']['failedAt']);

            self::assertFalse($outcomes['manual']['result']);
            self::assertSame('inner', $outcomes['manual']['failedAt']);
            self::assertSame('business rule rejected the row', $outcomes['manual']['error']);

            self::assertFalse($outcomes['cap']['result']);
            self::assertSame('reserve', $outcomes['cap']['failedAt'], "7th concurrent tx must hit cap=6");
            self::assertArrayNotHasKey('cap', $capturedCids, "Capped tx's closure must not have executed");

            // The six running transactions each ran on a distinct MySQL connection.
            $runningCids = array_values(array_filter($capturedCids, fn($v) => $v !== null));
            self::assertCount(6, $runningCids, "Six running txs captured a connection id");
            self::assertCount(
                6,
                array_unique($runningCids),
                "Each concurrent tx must run on a distinct MySQL connection; got " . json_encode($runningCids)
            );

            // --- 4) Verify final table state proves atomic rollback for failures ---
            $finalRows = $this->runOnLoop($loop, fn() => $factory->query("SELECT name, counter FROM `$table` ORDER BY name"));
            self::assertTrue($finalRows['result']);
            $names = array_column($finalRows['rows'], 'name');
            sort($names);
            self::assertSame(
                ['happy_name', 'seed'],
                $names,
                "Table must contain ONLY 'seed' and 'happy_name' — every failure scenario rolled back its insert"
            );

            $seedRow = $this->runOnLoop($loop, fn() => $factory->query("SELECT counter FROM `$table` WHERE name = 'seed'"));
            self::assertSame(1, (int) $seedRow['rows'][0]['counter'], "Happy tx must have incremented seed.counter to 1");

            // --- 5) Pool growth check ---
            $trace = $factory->getTrace();
            self::assertCount(6, $trace['workers']['write'] ?? [], "Write pool grew to cap=6");
        } finally {
            // Best-effort cleanup so we leave no schema footprint behind.
            try {
                $this->runOnLoop($loop, fn() => $factory->query("DROP TABLE IF EXISTS `$table`"));
            } catch (\Throwable) {
                // ignore — test outcome already established
            }
        }
    }

    /**
     * Helper: invoke $work() (which must return a PromiseInterface), run the
     * event loop until it settles, and return the resolved value (or throw on
     * rejection). Used for the synchronous setup/cleanup queries.
     */
    private function runOnLoop(LoopInterface $loop, callable $work): array
    {
        $result = null;
        $error = null;
        $work()->then(
            function ($v) use (&$result, $loop) {
                $result = $v;
                $loop->stop();
            },
            function ($e) use (&$error, $loop) {
                $error = $e;
                $loop->stop();
            }
        );
        $timer = $loop->addTimer(10, fn() => $loop->stop());
        $loop->run();
        $loop->cancelTimer($timer);
        if ($error !== null) {
            throw $error instanceof \Throwable ? $error : new \RuntimeException((string) $error);
        }
        if (!is_array($result)) {
            throw new \RuntimeException("Helper expected array result, got " . gettype($result));
        }
        return $result;
    }
}
