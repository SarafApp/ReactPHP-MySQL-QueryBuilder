<?php

namespace QueryBuilderTest\CoreTest;

require_once __DIR__ . '/FakeConnection.php';

use PHPUnit\Framework\TestCase;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\DBWorker;
use Saraf\QB\QueryBuilder\Exceptions\TransactionException;

final class DBFactoryReservationTest extends TestCase
{
    /**
     * Build a DBFactory bypassing its real constructor so tests don't open MySQL connections.
     * Optionally use the StubDBFactory subclass (below) to stub createWriteConnection for growth tests.
     */
    private function makeFactory(array $writeWorkers, int $maxWriteConnections, ?DBWorker $stubGrowthWorker = null): DBFactory
    {
        $factoryClass = $stubGrowthWorker === null ? DBFactory::class : StubDBFactory::class;

        $r = new \ReflectionClass($factoryClass);
        $factory = $r->newInstanceWithoutConstructor();

        $writeProp = (new \ReflectionClass(DBFactory::class))->getProperty('writeConnections');
        $writeProp->setAccessible(true);
        $writeProp->setValue($factory, $writeWorkers);

        $readProp = (new \ReflectionClass(DBFactory::class))->getProperty('readConnections');
        $readProp->setAccessible(true);
        $readProp->setValue($factory, []);

        $maxProp = (new \ReflectionClass(DBFactory::class))->getProperty('maxWriteConnections');
        $maxProp->setAccessible(true);
        $maxProp->setValue($factory, $maxWriteConnections);

        if ($stubGrowthWorker !== null) {
            /** @var StubDBFactory $factory */
            $factory->stubGrowthWorker = $stubGrowthWorker;
        }

        return $factory;
    }

    private function makeWorker(): DBWorker
    {
        return new DBWorker(new FakeConnection());
    }

    /** @test */
    public function reserveWriteConnectionWalksFreeWorkers()
    {
        $w1 = $this->makeWorker();
        $w2 = $this->makeWorker();
        $factory = $this->makeFactory([$w1, $w2], maxWriteConnections: 2);

        $a = $factory->reserveWriteConnection();
        $b = $factory->reserveWriteConnection();

        self::assertNotSame($a, $b);
        self::assertTrue($a->isReserved());
        self::assertTrue($b->isReserved());
    }

    /** @test */
    public function reserveThrowsWhenAtCapAndAllReserved()
    {
        $w1 = $this->makeWorker();
        $factory = $this->makeFactory([$w1], maxWriteConnections: 1);

        $factory->reserveWriteConnection();

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage("cap=1");
        $factory->reserveWriteConnection();
    }

    /** @test */
    public function reserveGrowsPoolOnDemandUpToCap()
    {
        $existing = $this->makeWorker();
        $existing->reserve();
        $grown = $this->makeWorker();

        $factory = $this->makeFactory([$existing], maxWriteConnections: 3, stubGrowthWorker: $grown);

        $returned = $factory->reserveWriteConnection();

        self::assertSame($grown, $returned);
        self::assertTrue($grown->isReserved());

        $writeProp = (new \ReflectionClass(DBFactory::class))->getProperty('writeConnections');
        $writeProp->setAccessible(true);
        self::assertCount(2, $writeProp->getValue($factory));
    }

    /** @test */
    public function getBestConnectionSkipsReservedWorkers()
    {
        $w1 = $this->makeWorker();
        $w2 = $this->makeWorker();
        $factory = $this->makeFactory([$w1, $w2], maxWriteConnections: 2);

        $reserved = $factory->reserveWriteConnection();

        $m = new \ReflectionMethod(DBFactory::class, 'getBestConnection');
        $m->setAccessible(true);
        $best = $m->invoke($factory);

        $writeProp = (new \ReflectionClass(DBFactory::class))->getProperty('writeConnections');
        $writeProp->setAccessible(true);
        $writeConnections = $writeProp->getValue($factory);

        self::assertNotNull($best['write']);
        self::assertNotSame($reserved, $writeConnections[$best['write']]);
    }

    /** @test */
    public function releaseReEnablesSelection()
    {
        $w1 = $this->makeWorker();
        $factory = $this->makeFactory([$w1], maxWriteConnections: 1);

        $reserved = $factory->reserveWriteConnection();
        $reserved->release();

        $again = $factory->reserveWriteConnection();
        self::assertSame($w1, $again);
    }
}

class StubDBFactory extends DBFactory
{
    public ?DBWorker $stubGrowthWorker = null;

    protected function createWriteConnection(): DBWorker
    {
        return $this->stubGrowthWorker;
    }
}
