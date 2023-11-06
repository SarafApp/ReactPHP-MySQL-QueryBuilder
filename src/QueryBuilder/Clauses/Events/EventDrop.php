<?php

namespace Saraf\QB\QueryBuilder\Clauses\Events;

use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;
use Saraf\QB\QueryBuilder\Exceptions\EventDropException;

class EventDrop
{
    protected ?string $eventName = null;

    protected string $query;

    public function __construct(protected DBFactory $factory)
    {
    }

    public function setEventName(?string $eventName): void
    {
        $this->eventName = $eventName;
    }

    /**
     * @throws \Saraf\QB\QueryBuilder\Exceptions\EventDropException
     */
    public function compile(): static
    {
        if (is_null($this->eventName))
            throw new EventDropException("Event name is null");

        $this->query = "DROP EVENT " . $this->eventName;
        return $this;
    }

    public function commit(): PromiseInterface|Promise
    {
        try {
            return $this->factory
                ->query($this->query);
        } catch (DBFactoryException $e) {
            return new Promise(function (callable $resolve) use ($e) {
                $resolve([
                    'result' => false,
                    'error' => $e->getMessage()
                ]);
            });
        }
    }

}