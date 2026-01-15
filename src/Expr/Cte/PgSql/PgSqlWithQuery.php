<?php

namespace AndrewGos\QueryBuilder\Expr\Cte\PgSql;

use AndrewGos\QueryBuilder\Expr\Cte\Cycle;
use AndrewGos\QueryBuilder\Expr\Cte\Search;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;

class PgSqlWithQuery extends WithQuery
{
    /**
     * @param MaybeReturnableQueryInterface $query
     * @param bool|null $materialized NULL by default to prevent materialization
     * @param Search|null $search
     * @param Cycle|null $cycle
     */
    public function __construct(
        MaybeReturnableQueryInterface $query,
        protected(set) ?bool $materialized = null,
        ?Search $search = null,
        ?Cycle $cycle = null,
    ) {
        parent::__construct($query, $search, $cycle);
    }
}
