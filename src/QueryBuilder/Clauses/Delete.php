<?php

namespace Saraf\QB\QueryBuilder\Clauses;

use Saraf\QB\QueryBuilder\Capability\From;
use Saraf\QB\QueryBuilder\Capability\Limit;
use Saraf\QB\QueryBuilder\Capability\Where;
use Saraf\QB\QueryBuilder\Core\Builder;
use Saraf\QB\QueryBuilder\Core\DBFactory;
use Saraf\QB\QueryBuilder\Core\EQuery;
use Saraf\QB\QueryBuilder\Core\Query;
use Saraf\QB\QueryBuilder\Exceptions\QueryBuilderException;

class Delete
{
    use From;
    use Where;
    use Limit;

    public function __construct(protected DBFactory|null $factory = null)
    {
    }

    /**
     * @throws QueryBuilderException
     */
    public function compile(): Query|EQuery
    {
        if (!isset($this->fromTable) || empty(trim($this->fromTable))) {
            throw new QueryBuilderException("From is Required");
        }

        $where = Builder::where($this->whereStatements);
        if (strlen($where) == 0) {
            throw new QueryBuilderException("Where is Required");
        }

        $baseQuery = Builder::setDeleteTable($this->fromTable);
        $baseQuery .= $where;

        if (isset($this->count)) {
            $baseQuery .= Builder::count($this->count);
            if (isset($this->offset)) {
                $baseQuery .= Builder::offset($this->offset);
            }
        }

        if (is_null($this->factory)) {
            return new Query($baseQuery);
        }

        return new EQuery($baseQuery, $this->factory);
    }
}
