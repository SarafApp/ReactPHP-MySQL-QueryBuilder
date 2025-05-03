<?php

namespace Saraf\QB\QueryBuilder\Core;

trait ReservableConnection
{
    public function reserveConnection(): DBWorker
    {
        return array_pop($this->writeConnections);
    }

    public function releaseConnection(DBWorker $connection): void
    {
        $this->writeConnections[] = $connection;
    }
}