<?php

namespace AndrewGos\QueryBuilder\Query\Update\PgSql;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Query\Interface\FromInterface;
use AndrewGos\QueryBuilder\Query\Interface\JoinInterface;
use AndrewGos\QueryBuilder\Query\Interface\PgSql\ReturningInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Trait\FromTrait;
use AndrewGos\QueryBuilder\Query\Trait\JoinTrait;
use AndrewGos\QueryBuilder\Query\Trait\PgSql\ReturningTrait;
use AndrewGos\QueryBuilder\Query\Update\UpdateQuery;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Update; CONCEPT(8): PgSqlUpdateQuery; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL-specific UPDATE query with FROM (additional tables), JOIN, and RETURNING clause support.
 * @scope PgSQL UPDATE query building.
 * @input Target table, SET values, optional FROM tables, JOIN conditions, WHERE, RETURNING.
 * @output PgSqlUpdateQuery instance with PostgreSQL-specific UPDATE capabilities
 * @invariants
 * - FROM clause is used for additional tables (not the target table — that comes from table()).
 * - Implements ReturningInterface for PostgreSQL RETURNING support.
 * @modulemap
 * PgSqlUpdateQuery => PgSQL UPDATE query with FROM, JOIN, RETURNING support
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, UPDATE, FROM, JOIN, RETURNING, dialect

// region CLASS_PgSqlUpdateQuery [DOMAIN(8): Update; CONCEPT(8): PgSqlUpdateQuery; TECH(8): Dialect]
/**
 * @template TSetValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null
 * @template TSet of array<int, SetClause>|array<string, TSetValue>
 * @purpose PostgreSQL UPDATE query extending UpdateQuery with FROM (additional tables), JOIN, and RETURNING clause support.
 */
class PgSqlUpdateQuery extends UpdateQuery implements
    FromInterface,
    JoinInterface,
    ReturningInterface
{
    use FromTrait;
    use JoinTrait;
    use ReturningTrait;
}
// endregion CLASS_PgSqlUpdateQuery
