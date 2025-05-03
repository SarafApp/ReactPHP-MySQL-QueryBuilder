<?php

namespace Saraf\QB\QueryBuilder\Clauses;

use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\DBWorker;
use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResult;
use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResultCollection;

class Transaction
{
    protected array $queries = [];
    protected QueryResultCollection $queryResultCollection;
    private ?DBWorker $connection = null;

    public function __construct(
        protected DBFactory $dbFactory,
    )
    {
        $this->queryResultCollection = new QueryResultCollection();
        $this->connection = $this->dbFactory->reserveConnection();
    }

    public function addQuery(string $name, Select|Update|Delete|Insert $query, \Closure $callback): static
    {
        $this->queries[] = compact('name', 'query', 'callback');
        return $this;
    }

    public function compile(): void
    {
        $this->connection->query("START TRANSACTION")
            ->then(function () {
                $this->resolveQueries();
            })
            ->finally(function () {
                $this->dbFactory->releaseConnection($this->connection);
            });
    }

    protected function resolveQueries(): \React\Promise\PromiseInterface
    {
        if (count($this->queries) === 0) {
            return $this->connection->query('COMMIT');
        }

        $queryItem = array_shift($this->queries);

        $query = $queryItem['query'];
        $callback = $queryItem['callback'];
        $name = $queryItem['name'];

        $queryString = null;
        $query->compile()->getQuery()
            ->then(function ($result) use (&$queryString) {
                $queryString = $result['query'];
            });

        return $this->connection->query($queryString)
            ->then(function ($result) use ($query, $callback, $name) {
                if (!$result['result']) {
                    return $this->connection->query('ROLLBACK');
                }

                $queryResult = new QueryResult(
                    $result['result'],
                    @$result['count'] ?? null,
                    @$result['rows'] ?? [],
                    @$result['affectedRows'] ?? null,
                    @$result['insertId'] ?? null,
                );

                if ($callback($queryResult, $this->queryResultCollection)) {
                    $this->queryResultCollection->add($name, $queryResult);
                    return $this->resolveQueries();
                }

                return $this->connection->query('ROLLBACK');
            });
    }
}