<?php

namespace AndrewGos\QueryBuilder\Expr\Table\PgSql;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Table; CONCEPT(8): PgSqlSelectTable; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose Provides PostgreSQL-specific table expression with ONLY modifier for table inheritance.
 * @scope PostgreSQL SELECT table references with ONLY support.
 * @input string $name, bool $only
 * @output PgSqlSelectTable instance extending SelectTable
 * @invariants
 * - $only=true generates ONLY table_name to exclude child tables
 * @modulemap
 * PgSqlSelectTable => PostgreSQL table reference with ONLY modifier
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, table, ONLY, inheritance, SELECT, dialect

// region CLASS_PgSqlSelectTable [DOMAIN(8): Table; CONCEPT(8): PgSqlSelectTable; TECH(8): Dialect]
/**
 * @purpose Extends SelectTable to support the PostgreSQL ONLY modifier for table inheritance hierarchies.
 */
class PgSqlSelectTable extends SelectTable
{
    public function __construct(
        string $name,
        protected(set) bool $only = false,
    ) {
        parent::__construct($name);
    }
}
// endregion CLASS_PgSqlSelectTable
