<?php

namespace QueryBuilderTest\CoreTest;

require_once __DIR__ . '/FakeConnection.php';

use PHPUnit\Framework\TestCase;
use React\MySQL\QueryResult;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\DBWorker;
use Saraf\QB\QueryBuilder\Core\Transaction;

final class TransactionTest extends TestCase
{
    private FakeConnection $fake;
    private DBWorker $worker;
    private DBFactory $factory;
    private Transaction $tx;

    protected function setUp(): void
    {
        $this->fake = new FakeConnection();
        $this->worker = new DBWorker($this->fake);
        $this->worker->reserve();

        $r = new \ReflectionClass(DBFactory::class);
        $this->factory = $r->newInstanceWithoutConstructor();

        $this->tx = new Transaction($this->factory, $this->worker);
    }

    private static function okWithRows(): QueryResult
    {
        $qr = new QueryResult();
        $qr->resultRows = [];
        $qr->resultFields = [];
        return $qr;
    }

    private static function okAffected(int $affected = 1, int $insertId = 0): QueryResult
    {
        $qr = new QueryResult();
        $qr->affectedRows = $affected;
        $qr->insertId = $insertId;
        return $qr;
    }

    /** @test */
    public function happyPathCommitsAndCapturesClosureValue()
    {
        $outer = null;
        $this->tx->run(function ($tx) {
            return $tx->insert()
                ->into("Orders")->setColumns(["id"])->addRow([1])
                ->compile()->commit()
                ->then(fn() => $tx->update()
                    ->table("Stock")->setUpdates(['n' => 1])->where("id", 1)
                    ->compile()->commit()
                    ->then(fn() => ['marker' => 'done']));
        })->then(function ($r) use (&$outer) {
            $outer = $r;
        });

        self::assertSame(
            [
                "START TRANSACTION",
                "INSERT INTO `Orders` (`id`) VALUES (1)",
                "UPDATE `Stock` SET `n` = 1 WHERE (`id` = 1)",
                "COMMIT",
            ],
            $this->fake->recorded
        );
        self::assertTrue($outer['result']);
        self::assertSame(['marker' => 'done'], $outer['data']);
        self::assertFalse($this->worker->isReserved());
    }

    /** @test */
    public function innerResultFalseTriggersRollback()
    {
        $outer = null;
        $this->tx->run(function ($tx) {
            return $tx->insert()
                ->into("Orders")->setColumns(["id"])->addRow([1])
                ->compile()->commit()
                ->then(fn() => ['result' => false, 'error' => 'bad data']);
        })->then(function ($r) use (&$outer) {
            $outer = $r;
        });

        self::assertSame(
            ["START TRANSACTION", "INSERT INTO `Orders` (`id`) VALUES (1)", "ROLLBACK"],
            $this->fake->recorded
        );
        self::assertFalse($outer['result']);
        self::assertSame('inner', $outer['failedAt']);
        self::assertSame('bad data', $outer['error']);
        self::assertFalse($this->worker->isReserved());
    }

    /** @test */
    public function closureSyncThrowTriggersRollback()
    {
        $outer = null;
        $this->tx->run(function ($tx) {
            throw new \RuntimeException("kaboom");
        })->then(function ($r) use (&$outer) {
            $outer = $r;
        });

        self::assertSame(["START TRANSACTION", "ROLLBACK"], $this->fake->recorded);
        self::assertFalse($outer['result']);
        self::assertSame('closure', $outer['failedAt']);
        self::assertSame('kaboom', $outer['error']);
        self::assertFalse($this->worker->isReserved());
    }

    /** @test */
    public function innerPromiseRejectionTriggersRollback()
    {
        $outer = null;
        $this->tx->run(function ($tx) {
            return \React\Promise\reject(new \RuntimeException("downstream error"));
        })->then(function ($r) use (&$outer) {
            $outer = $r;
        });

        self::assertSame(["START TRANSACTION", "ROLLBACK"], $this->fake->recorded);
        self::assertFalse($outer['result']);
        self::assertSame('inner', $outer['failedAt']);
        self::assertSame('downstream error', $outer['error']);
        self::assertFalse($this->worker->isReserved());
    }

    /** @test */
    public function commitFailureTriggersRollback()
    {
        $this->fake->respondWith("COMMIT", fn() => new \RuntimeException("commit broken"));

        $outer = null;
        $this->tx->run(function ($tx) {
            return $tx->insert()
                ->into("Orders")->setColumns(["id"])->addRow([1])
                ->compile()->commit()
                ->then(fn() => ['ok' => true]);
        })->then(function ($r) use (&$outer) {
            $outer = $r;
        });

        self::assertSame(
            ["START TRANSACTION", "INSERT INTO `Orders` (`id`) VALUES (1)", "COMMIT", "ROLLBACK"],
            $this->fake->recorded
        );
        self::assertFalse($outer['result']);
        self::assertSame('commit', $outer['failedAt']);
        self::assertSame('commit broken', $outer['error']);
        self::assertFalse($this->worker->isReserved());
    }

    /** @test */
    public function beginFailureSkipsClosureAndRollback()
    {
        $this->fake->respondWith("START TRANSACTION", fn() => new \RuntimeException("can't begin"));

        $closureRan = false;
        $outer = null;
        $this->tx->run(function ($tx) use (&$closureRan) {
            $closureRan = true;
            return \React\Promise\resolve(null);
        })->then(function ($r) use (&$outer) {
            $outer = $r;
        });

        self::assertFalse($closureRan);
        self::assertSame(["START TRANSACTION"], $this->fake->recorded);
        self::assertFalse($outer['result']);
        self::assertSame('begin', $outer['failedAt']);
        self::assertSame("can't begin", $outer['error']);
        self::assertFalse($this->worker->isReserved());
    }

    /** @test */
    public function inTransactionSelectUsesReservedWorker()
    {
        $this->fake->respondWith("SELECT", fn() => self::okWithRows());

        $outer = null;
        $this->tx->run(function ($tx) {
            return $tx->select()->from("Customers")->addAllColumns()
                ->compile()->commit()
                ->then(fn($r) => $r);
        })->then(function ($r) use (&$outer) {
            $outer = $r;
        });

        self::assertSame(
            ["START TRANSACTION", "SELECT * FROM `Customers` ", "COMMIT"],
            $this->fake->recorded
        );
        self::assertTrue($outer['result']);
        self::assertFalse($this->worker->isReserved());
    }

    /** @test */
    public function rawEscapeHatchRunsOnReservedWorker()
    {
        $this->fake->respondWith("SELECT @@hostname", fn() => self::okWithRows());

        $outer = null;
        $this->tx->run(function ($tx) {
            return $tx->raw("SELECT @@hostname");
        })->then(function ($r) use (&$outer) {
            $outer = $r;
        });

        self::assertSame(
            ["START TRANSACTION", "SELECT @@hostname", "COMMIT"],
            $this->fake->recorded
        );
        self::assertTrue($outer['result']);
        self::assertFalse($this->worker->isReserved());
    }
}
