<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Filtering; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for SQL WHERE clause with AND/OR composable conditions.
 * @scope Methods to set, append with AND, or append with OR conditions.
 * @input Condition arrays or ExprInterface with nested logical structure.
 * @output Contract for row filtering.
 * @modulemap
 * INTERFACE WhereInterface => WHERE clause contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: WHERE, conditions, filtering, SQL, AND, OR, expressions
// STRUCTURE: ▶ ┌where property┐ + where() + andWhere() + orWhere(OrExpr) → ∑ [WhereInterface contract]

// region INTERFACE_WhereInterface [DOMAIN(8): Query; CONCEPT(9): Filtering; TECH(8): SQL]
/**
 * This interface provides methods for working with WHERE clause
 *
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 *
 * @template TCondition of TValue|array<TCondition>
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, TStandaloneCondition>
 * @purpose Define methods for working with WHERE clause.
 */
interface WhereInterface
{
    /**
     * @var TConditions
     */
    public array $where {
        get;
    }

    // region METHOD_where [DOMAIN(8): Query; CONCEPT(9): Filtering; TECH(8): SQL]
    /**
     * @purpose Set the WHERE conditions, replacing any existing ones.
     * @io TConditions|ExprInterface $conditions -> static
     * @complexity 2
     *
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function where(array|ExprInterface $conditions): static;
    // endregion METHOD_where

    // region METHOD_andWhere [DOMAIN(8): Query; CONCEPT(9): Filtering; TECH(8): SQL]
    /**
     * @purpose Append additional conditions with AND logic to existing WHERE.
     * @io TConditions|ExprInterface $conditions -> static
     * @complexity 2
     *
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function andWhere(array|ExprInterface $conditions): static;
    // endregion METHOD_andWhere

    // region METHOD_orWhere [DOMAIN(8): Query; CONCEPT(9): Filtering; TECH(8): SQL]
    /**
     * @purpose Append conditions with OR logic, wrapping existing AND group.
     * @io TConditions|ExprInterface $conditions -> static
     * @complexity 3
     *
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function orWhere(array|ExprInterface $conditions): static;
    // endregion METHOD_orWhere
}
// endregion INTERFACE_WhereInterface
