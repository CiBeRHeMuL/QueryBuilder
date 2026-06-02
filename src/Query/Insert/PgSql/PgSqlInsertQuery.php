<?php

namespace AndrewGos\QueryBuilder\Query\Insert\PgSql;

use AndrewGos\QueryBuilder\Enum\Insert\PgSql\PgSqlOverrideValueMethodEnum;
use AndrewGos\QueryBuilder\Query\Insert\InsertQuery;
use AndrewGos\QueryBuilder\Query\Interface\PgSql\ReturningInterface;
use AndrewGos\QueryBuilder\Query\Trait\PgSql\ReturningTrait;

// region MODULE_CONTRACT [DOMAIN(8): Insert; CONCEPT(8): PgSqlInsertQuery; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL-specific INSERT query with OVERRIDING USER/SYSTEM VALUE and RETURNING clause support.
 * @scope PostgreSQL INSERT query building.
 * @input Table, columns, values, returning columns
 * @output PgSqlInsertQuery instance with PostgreSQL-specific INSERT capabilities
 * @invariants
 * - Implements ReturningInterface for RETURNING clause
 * @modulemap
 * PgSqlInsertQuery => PostgreSQL INSERT query with OVERRIDING and RETURNING
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, INSERT, OVERRIDING, RETURNING, dialect

// region CLASS_PgSqlInsertQuery [DOMAIN(8): Insert; CONCEPT(8): PgSqlInsertQuery; TECH(8): Dialect]
/**
 * @purpose PostgreSQL INSERT query extending InsertQuery with OVERRIDING USER/SYSTEM VALUE method and RETURNING clause support.
 */
class PgSqlInsertQuery extends InsertQuery implements ReturningInterface
{
    use ReturningTrait;

    protected(set) ?PgSqlOverrideValueMethodEnum $overrideValueMethod = null;
}
// endregion CLASS_PgSqlInsertQuery
