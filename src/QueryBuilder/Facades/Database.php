<?php

namespace Saraf\QB\QueryBuilder\Facades;

use Saraf\QB\QueryBuilder\Abstracts\Facade;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\QueryBuilder;
use Saraf\QB\QueryBuilder\QueryBuilderManager;

/**
 * @method static void initConnection(DBFactory $DBFactory)
 * @method static QueryBuilder query()
 */
class Database extends Facade
{
    protected static function getFacadeAccessor(): QueryBuilderManager
    {
        return QueryBuilderManager::instance();
    }
}