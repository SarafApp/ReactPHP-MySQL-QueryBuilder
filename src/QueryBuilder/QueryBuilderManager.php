<?php

namespace Saraf\QB\QueryBuilder;

use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Exceptions\DBFactoryException;

class QueryBuilderManager
{
    private static ?self $instance = null;
    private DBFactory $connection;

    private function __construct()
    {
    }

    public static function instance(): QueryBuilderManager
    {
        if (is_null(self::$instance)) {

            self::$instance = new self();
        }

        return self::$instance;
    }

    public function initConnection(
        DBFactory $DBFactory,
    ): void
    {
        $this->connection = $DBFactory;
    }


    /**
     * @throws DBFactoryException
     */
    public function query(): QueryBuilder
    {
        return $this->connection->getQueryBuilder();
    }
}