<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Cte\PgSql;

use AndrewGos\QueryBuilder\Expr\Cte\Cycle;
use AndrewGos\QueryBuilder\Expr\Cte\Search;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): CTE; CONCEPT(8): PgSqlWithQuery; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose Provides PostgreSQL-specific WITH query extensions (MATERIALIZED/NOT MATERIALIZED support).
 * @scope PgSql CTE with materialization hints, SEARCH, and CYCLE clauses.
 * @input MaybeReturnableQueryInterface $query, ?bool $materialized, ?Search $search, ?Cycle $cycle
 * @output PgSqlWithQuery instance extending WithQuery
 * @invariants
 * - $materialized null means no materialization hint
 * @modulemap
 * PgSqlWithQuery => PostgreSQL-specific WITH query with materialization
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, CTE, WITH, materialized, SEARCH, CYCLE, dialect
// STRUCTURE: ▶ __construct ┌query + parent(search, cycle) + materialized┐ → ∑ [PgSqlWithQuery extends WithQuery]

// region CLASS_PgSqlWithQuery [DOMAIN(8): CTE; CONCEPT(8): PgSqlWithQuery; TECH(8): Dialect]
/**
 * @purpose Extends WithQuery to support PostgreSQL MATERIALIZED/NOT MATERIALIZED hints and SEARCH/CYCLE clause extensions.
 */
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
// endregion CLASS_PgSqlWithQuery
