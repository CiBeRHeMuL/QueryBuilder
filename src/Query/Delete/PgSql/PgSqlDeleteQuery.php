<?php

namespace AndrewGos\QueryBuilder\Query\Delete\PgSql;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Order\OrderColumn;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Delete\DeleteQuery;
use AndrewGos\QueryBuilder\Query\Interface\JoinInterface;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Interface\PgSql\ReturningInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Trait\JoinTrait;
use AndrewGos\QueryBuilder\Query\Trait\PgSql\ReturningTrait;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Delete; CONCEPT(8): PgSqlDeleteQuery; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL-specific DELETE query with USING, JOIN, RETURNING clauses and returnable query support.
 * @scope PostgreSQL DELETE query building with advanced clauses.
 * @input Tables, conditions, using tables, joins, returning columns
 * @output PgSqlDeleteQuery instance with PostgreSQL-specific DELETE capabilities
 * @invariants
 * - DELETE is returnable only if RETURNING clause is specified
 * @modulemap
 * PgSqlDeleteQuery => PostgreSQL DELETE query with USING, JOIN, and RETURNING
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, DELETE, USING, JOIN, RETURNING, dialect

// region CLASS_PgSqlDeleteQuery [DOMAIN(8): Delete; CONCEPT(8): PgSqlDeleteQuery; TECH(8): Dialect]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @template TExpression of ExprInterface|SelectQueryInterface
 *
 * @template TSelectExpression of SelectQueryInterface
 *
 * @template TGroupValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TGroupExpression of ExprInterface|UnitEnum
 *
 * @template TTable of string|ExprInterface|SelectQueryInterface|SelectTable
 * @template TNormalizedTable of ExprInterface|SelectQueryInterface|SelectTable
 *
 * @template TCondition of ExprInterface|ExprInterface
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of ExprInterface|array<int, bool|ExprInterface>
 *
 * @template TOrderBy of array<string, int|string>|array<int, string|ExprInterface|OrderColumn> column => order, expression or OrderColumn
 * @purpose PostgreSQL DELETE query extending DeleteQuery with USING, JOIN, RETURNING clauses and MaybeReturnableQueryInterface implementation.
 */
class PgSqlDeleteQuery extends DeleteQuery implements JoinInterface, ReturningInterface, MaybeReturnableQueryInterface
{
    use ReturningTrait;
    use JoinTrait;

    /**
     * @var array<int|string, TNormalizedTable>
     */
    protected(set) array $using = [];

    // region METHOD_using [DOMAIN(8): Delete; TECH(8): Using]
    /**
     * @param array<int|string, TTable> $tables
     *
     * @return static
     * @purpose Set USING tables for PostgreSQL DELETE.
     */
    public function using(array $tables): static
    {
        $this->using = array_map(HExpr::normalizeTable(...), $tables);

        return $this;
    }
    // endregion METHOD_using

    // region METHOD_addUsing [DOMAIN(8): Delete; TECH(8): Using]
    /**
     * @param array<int|string, TTable> $tables
     *
     * @return static
     * @purpose Add additional USING tables for PostgreSQL DELETE.
     */
    public function addUsing(array $tables): static
    {
        $this->using = array_merge(
            $this->using,
            array_map(HExpr::normalizeTable(...), $tables),
        );
        return $this;
    }
    // endregion METHOD_addUsing

    // region METHOD_isReturnable [DOMAIN(8): Delete; TECH(8): Returning]
    /**
     * For PostgreSQL we can use result of DELETE statement only if RETURNING clause is specified
     *
     * @return bool
     * @purpose Check if DELETE query is returnable (has RETURNING clause).
     */
    public function isReturnable(): bool
    {
        return $this->returningColumns !== null;
    }
    // endregion METHOD_isReturnable
}
// endregion CLASS_PgSqlDeleteQuery
