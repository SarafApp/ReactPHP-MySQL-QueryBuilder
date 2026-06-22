<?php

namespace QueryBuilderTest\CoreTest;

use Evenement\EventEmitterTrait;
use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use function React\Promise\reject;
use function React\Promise\resolve;

final class FakeConnection implements ConnectionInterface
{
    use EventEmitterTrait;

    /** @var string[] */
    public array $recorded = [];

    /** @var array<string, callable> map of SQL prefix => callable(string $sql): QueryResult|\Throwable */
    private array $responders = [];

    public function respondWith(string $prefix, callable $responder): void
    {
        $this->responders[$prefix] = $responder;
    }

    public function query($sql, array $params = [])
    {
        $this->recorded[] = $sql;
        foreach ($this->responders as $prefix => $responder) {
            if (str_starts_with($sql, $prefix)) {
                $value = $responder($sql);
                if ($value instanceof \Throwable) {
                    return reject($value);
                }
                return resolve($value);
            }
        }
        $result = new QueryResult();
        $result->affectedRows = 0;
        return resolve($result);
    }

    public function queryStream($sql, $params = [])
    {
        throw new \LogicException("not used in tests");
    }

    public function ping()
    {
        return resolve(null);
    }

    public function quit()
    {
        return resolve(null);
    }

    public function close()
    {
    }
}
