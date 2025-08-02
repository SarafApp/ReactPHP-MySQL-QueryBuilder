<?php

namespace Saraf\QB\QueryBuilder\Clauses;

use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\DBWorker;
use Saraf\QB\QueryBuilder\Exceptions\TransactionException;
use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResult;
use Saraf\QB\QueryBuilder\Helpers\QueryResult\QueryResultCollection;
use function React\Promise\reject;
use function React\Promise\resolve;

class Transaction
{
    protected array $queries = [];
    protected QueryResultCollection $queryResultCollection;
    private ?DBWorker $connection = null;

    public function __construct(
        protected ?DBFactory $dbFactory = null,
    )
    {
        $this->queryResultCollection = new QueryResultCollection();
        $this->connection = !is_null($this->dbFactory) ? $this->dbFactory->reserveConnection() : null;
    }

    public function addQuery(string $name, Select|Update|Delete|Insert $query, ?\Closure $callback = null): static
    {
        $this->queries[] = compact('name', 'query', 'callback');
        return $this;
    }

    /**
     * @throws TransactionException
     */
    public function compile(): \React\Promise\PromiseInterface
    {
        if (count($this->queries) === 0) {
            throw new TransactionException('There are no queries inside transaction.');
        }

        return $this->connection->query("START TRANSACTION")
            ->then(function () {
                return $this->resolveQueries();
            })
            ->finally(function () {
                $this->dbFactory->releaseConnection($this->connection);
            });
    }

    protected function resolveQueries(): \React\Promise\PromiseInterface
    {
        if (count($this->queries) === 0) {
            $this->connection->query('COMMIT');
            return resolve($this->queryResultCollection->last()->toArray());
        }

        $queryItem = array_shift($this->queries);

        $query = $queryItem['query'];
        $callback = $queryItem['callback'];
        $name = $queryItem['name'];

        return $query->compile()->getQuery()->then(function ($result) use ($query, $callback, $name) {
            $queryString = $result['query'];
            return $this->connection->query($queryString)
                ->then(function ($result) use ($query, $callback, $name, $queryString) {
                    if (!$result['result']) {
                        $this->connection->query('ROLLBACK');
                        return reject(throw new TransactionException('Transaction rolled back due to ' . $result['error']));
                    }

                    $queryResult = new QueryResult(
                        $result['result'],
                        @$result['count'] ?? null,
                        @$result['rows'] ?? [],
                        @$result['affectedRows'] ?? null,
                        @$result['insertId'] ?? null,
                    );

                    if (is_null($callback) || $callback($queryResult, $this->queryResultCollection)) {
                        $this->queryResultCollection->add($name, $queryResult);
                        return $this->resolveQueries();
                    }

                    $this->connection->query('ROLLBACK');
                    return reject(throw new TransactionException("Transaction rolled back,callback for query {$queryString} doesn't return true"));
                });
        });
    }
}