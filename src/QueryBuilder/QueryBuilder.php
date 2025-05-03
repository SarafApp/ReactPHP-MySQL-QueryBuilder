<?php

namespace Saraf\QB\QueryBuilder;

use Saraf\QB\QueryBuilder\Clauses\Delete;
use Saraf\QB\QueryBuilder\Clauses\Events\EventCreate;
use Saraf\QB\QueryBuilder\Clauses\Events\EventDrop;
use Saraf\QB\QueryBuilder\Clauses\Insert;
use Saraf\QB\QueryBuilder\Clauses\InsertUpdate;
use Saraf\QB\QueryBuilder\Clauses\MultiInsertUpdate;
use Saraf\QB\QueryBuilder\Clauses\Select;
use Saraf\QB\QueryBuilder\Clauses\Transaction;
use Saraf\QB\QueryBuilder\Clauses\Update;
use Saraf\QB\QueryBuilder\Core\DBFactory;

class QueryBuilder
{
    public function __construct(protected DBFactory|null $factory = null)
    {
    }

    public function eventCreate(): EventCreate
    {
        return new EventCreate($this->factory);
    }

    public function eventDrop(): EventDrop
    {
        return new EventDrop($this->factory);
    }

    public function select(): Select
    {
        return new Select($this->factory);
    }

    public function insert(): Insert
    {
        return new Insert($this->factory);
    }

    public function update(): Update
    {
        return new Update($this->factory);
    }

    public function insertUpdate(): InsertUpdate
    {
        return new InsertUpdate($this->factory);
    }

    public function multiInsertUpdate(): MultiInsertUpdate
    {
        return new MultiInsertUpdate($this->factory);
    }

    public function delete(): Delete
    {
        return new Delete($this->factory);
    }

    public function beginTransaction(): Transaction
    {
        return new Transaction($this->factory);
    }
}