<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Select;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Expr\Window\Window;
use AndrewGos\QueryBuilder\Query\Interface\FromInterface;
use AndrewGos\QueryBuilder\Query\Interface\JoinInterface;
use AndrewGos\QueryBuilder\Query\Interface\LimitInterface;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Interface\OperationsInterface;
use AndrewGos\QueryBuilder\Query\Interface\OrderByInterface;
use AndrewGos\QueryBuilder\Query\Interface\WhereInterface;
use AndrewGos\QueryBuilder\Query\Interface\WithInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the full contract for SELECT SQL queries with all standard clauses.
 * @scope Interface extending 7 clause interfaces + MaybeReturnableQueryInterface.
 * @input Columns, tables, conditions, grouping, window functions, ordering, limits, locks, set operations.
 * @output Contract for complete SELECT query DTO.
 * @modulemap
 * INTERFACE SelectQueryInterface => SELECT query contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: SELECT, SQL, query, select columns, FROM, JOIN, WHERE, GROUP BY, HAVING, WINDOW, ORDER BY, LIMIT, LOCK, UNION
// STRUCTURE: ▶ WithInterface + WhereInterface + FromInterface + JoinInterface + OperationsInterface + OrderByInterface + LimitInterface + MaybeReturnableQueryInterface + select()/distinct()/groupBy()/having()/window()/lock() → ∑ [SelectQueryInterface contract]

// region INTERFACE_SelectQueryInterface [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @template TExpression of TValue|array<TExpression>
 *
 * @template TSelectExpression of TExpression
 *
 * @template TGroupValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TGroupExpression of TGroupValue|array<TGroupExpression>
 *
 * @template TCondition of TValue|array<TCondition>
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, TStandaloneCondition>
 * @purpose Define the contract for SELECT SQL queries.
 */
interface SelectQueryInterface extends
    WithInterface,
    WhereInterface,
    FromInterface,
    JoinInterface,
    OperationsInterface,
    OrderByInterface,
    LimitInterface,
    MaybeReturnableQueryInterface
{
    /**
     * @var array<int|string, TSelectExpression>
     */
    public array $selectColumns {
        get;
    }
    public bool $distinct {
        get;
    }
    /**
     * @var TGroupExpression[]
     */
    public array $groupBy {
        get;
    }
    public bool $groupDistinct {
        get;
    }
    /**
     * @var TConditions
     */
    public array $having {
        get;
    }
    /**
     * @var array<string, Window>
     */
    public array $windows {
        get;
    }
    public ?LockModeInterface $lockMode {
        get;
    }

    // region METHOD_select [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Set the SELECT columns, replacing any existing ones.
     * @io array<int|string, TSelectExpression> $columns -> static
     * @complexity 2
     *
     * @param array<int|string, TSelectExpression> $columns
     *
     * @return static
     */
    public function select(array $columns): static;
    // endregion METHOD_select

    // region METHOD_addSelect [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Append additional columns to the existing SELECT list.
     * @io array<int|string, TSelectExpression> $columns -> static
     * @complexity 2
     *
     * @param array<int|string, TSelectExpression> $columns
     *
     * @return static
     */
    public function addSelect(array $columns): static;
    // endregion METHOD_addSelect

    // region METHOD_distinct [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Enable or disable DISTINCT mode on the SELECT.
     * @io bool $distinct -> static
     * @complexity 1
     *
     * @param bool $distinct
     *
     * @return static
     */
    public function distinct(bool $distinct = true): static;
    // endregion METHOD_distinct

    // region METHOD_groupBy [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Set GROUP BY columns, optionally with DISTINCT modifier.
     * @io TGroupExpression[] $columns, bool $distinct -> static
     * @complexity 2
     *
     * @param TGroupExpression[] $columns
     * @param bool $distinct
     *
     * @return static
     */
    public function groupBy(array $columns, bool $distinct = false): static;
    // endregion METHOD_groupBy

    // region METHOD_addGroupBy [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Append additional columns to the existing GROUP BY clause.
     * @io TGroupExpression[] $columns, bool $distinct -> static
     * @complexity 2
     *
     * @param TGroupExpression[] $columns
     * @param bool $distinct
     *
     * @return static
     */
    public function addGroupBy(array $columns, bool $distinct = false): static;
    // endregion METHOD_addGroupBy

    // region METHOD_having [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Set HAVING conditions, replacing any existing ones.
     * @io TConditions|ExprInterface $conditions -> static
     * @complexity 2
     *
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function having(array|ExprInterface $conditions): static;
    // endregion METHOD_having

    // region METHOD_andHaving [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Append conditions with AND logic to existing HAVING clause.
     * @io TConditions|ExprInterface $conditions -> static
     * @complexity 2
     *
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function andHaving(array|ExprInterface $conditions): static;
    // endregion METHOD_andHaving

    // region METHOD_orHaving [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Append conditions with OR logic, wrapping existing AND group in HAVING.
     * @io TConditions|ExprInterface $conditions -> static
     * @complexity 3
     *
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function orHaving(array|ExprInterface $conditions): static;
    // endregion METHOD_orHaving

    // region METHOD_window [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Define a named WINDOW for use with window functions.
     * @io string $name, Window $windowDefinition -> static
     * @complexity 2
     *
     * @param string $name
     * @param Window $windowDefinition
     *
     * @return static
     */
    public function window(string $name, Window $windowDefinition): static;
    // endregion METHOD_window

    // region METHOD_lock [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Apply a lock mode (FOR UPDATE, FOR NO KEY UPDATE, FOR SHARE, FOR KEY SHARE, optionally with NOWAIT/SKIP LOCKED) to the SELECT.
     * @io LockModeInterface|null $lockMode -> static
     * @complexity 2
     *
     * @param LockModeInterface|null $lockMode
     *
     * @return static
     */
    public function lock(?LockModeInterface $lockMode): static;
    // endregion METHOD_lock
}
// endregion INTERFACE_SelectQueryInterface
