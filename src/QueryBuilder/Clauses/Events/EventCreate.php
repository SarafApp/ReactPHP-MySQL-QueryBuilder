<?php

namespace Saraf\QB\QueryBuilder\Clauses\Events;


use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\EQuery;
use Saraf\QB\QueryBuilder\Core\Query;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;
use Saraf\QB\QueryBuilder\Exceptions\EventCreateException;
use function React\Async\await;

class EventCreate
{
    protected string|Query|EQuery $taskQuery;

    protected ?string $name = null;
    protected ?SchedulerModel $periods = null;

    protected ?SchedulerModel $starts = null;
    protected ?SchedulerModel $ends = null;

    protected ?string $query = null;

    public function __construct(protected ?DBFactory $factory = null)
    {
    }

    public function setEventName(string $eventName): static
    {
        $this->name = $eventName;
        return $this;
    }

    public function setTimePeriod(int $amount, string $periods): static
    {
        $this->periods = new SchedulerModel($amount, $periods);
        return $this;
    }

    public function setStartTime(int $amount, string $periods): static
    {
        $this->starts = new SchedulerModel($amount, $periods);
        return $this;
    }

    public function setEndTime(int $amount, string $periods): static
    {
        $this->ends = new SchedulerModel($amount, $periods);
        return $this;
    }

    public function setTaskQuery(string $taskQuery): static
    {
        $this->taskQuery = $taskQuery;
        return $this;
    }

    public function setTaskQueryObject(Query|EQuery $query): static
    {
        $this->taskQuery = $query;
        return $this;
    }

    /**
     * @throws \Throwable
     * @throws \Saraf\QB\QueryBuilder\Exceptions\EventCreateException
     */
    public function compile(): static
    {
        $finalQuery = "create event " . $this->name . " on schedule every ";
        if (is_null($this->periods))
            throw new EventCreateException("Time Period Error");

        $finalQuery .= $this->periods->getAmount() . " " . $this->periods->getPeriods() . " ";

        if (!is_null($this->starts))
            $finalQuery .= "starts CURRENT_TIMESTAMP + interval " . $this->starts->getAmount() . " " . $this->starts->getPeriods() . " ";

        if (!is_null($this->ends))
            $finalQuery .= "ends CURRENT_TIMESTAMP + interval " . $this->ends->getAmount() . " " . $this->ends->getPeriods() . " ";

        $finalQuery .= "do ";

        if ($this->taskQuery instanceof Query) {
            $finalQuery .= $this->taskQuery->getQueryAsString();
        } else if ($this->taskQuery instanceof EQuery) {
            $result = await($this->taskQuery->getQuery());
            if (!$result['result'])
                throw new EventCreateException($result['error']);
            $finalQuery .= $result['query'];
        } else {
            $finalQuery .= $this->taskQuery;
        }

        $this->query = $finalQuery;
        echo $finalQuery . PHP_EOL;
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