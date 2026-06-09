<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Merge\PgSql;

use AndrewGos\QueryBuilder\Query\Interface\PgSql\ReturningInterface;
use AndrewGos\QueryBuilder\Query\Merge\MergeQuery;
use AndrewGos\QueryBuilder\Query\Trait\PgSql\ReturningTrait;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(9): PgSqlMergeQuery; TECH(8): PgSql]
/**
 * @moduleContract
 * @purpose PostgreSQL-specific MERGE query extending ANSI MergeQuery with RETURNING support.
 * @scope PgSQL MERGE query DTO.
 * @input All ANSI MERGE inputs + RETURNING columns.
 * @output Immutable PgSQL MERGE query DTO.
 * @invariants
 * - ReturningTrait makes this query returnable when RETURNING is set.
 * @modulemap
 * PgSqlMergeQuery => PostgreSQL MERGE query with RETURNING
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PgSqlMergeQuery, PostgreSQL, MERGE, RETURNING, DO NOTHING, query
// STRUCTURE: ▶ MergeQuery + ReturningTrait + returning() → ∑ [PgSqlMergeQuery]

// region CLASS_PgSqlMergeQuery [DOMAIN(8): Merge; CONCEPT(9): PgSqlMergeQuery; TECH(8): PgSql]
/**
 * @purpose PostgreSQL MERGE query with RETURNING clause (via ReturningTrait). WHEN NOT MATCHED BY SOURCE is inherited from MergeQuery (ANSI SQL:2008).
 */
class PgSqlMergeQuery extends MergeQuery implements ReturningInterface
{
    use ReturningTrait;
}
// endregion CLASS_PgSqlMergeQuery
