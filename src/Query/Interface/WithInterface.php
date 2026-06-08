<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): CTE; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for SQL WITH (Common Table Expression) clause.
 * @scope Methods to set and extend CTE definitions with recursive support.
 * @input Named WithQuery definitions and recursive flag.
 * @output Contract for CTE clause on any query type.
 * @modulemap
 * INTERFACE WithInterface => WITH clause contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: WITH, CTE, common table expression, SQL, recursive, subquery
// STRUCTURE: ▶ ┌with property, withRecursive┐ + with() + addWith() → ∑ [WithInterface contract]

// region INTERFACE_WithInterface [DOMAIN(8): Query; CONCEPT(9): CTE; TECH(8): SQL]
/**
 * @purpose Define the contract for SQL WITH (Common Table Expression) clause.
 */
interface WithInterface
{
    /**
     * @var array<string, WithQuery>
     */
    public array $with {
        get;
    }
    public bool $withRecursive {
        get;
    }

    // region METHOD_with [DOMAIN(8): Query; CONCEPT(9): CTE; TECH(8): SQL]
    /**
     * @purpose Set CTE definitions for the query, optionally enabling RECURSIVE mode.
     * @io array<string, WithQuery> $with, bool $recursive -> static
     * @complexity 2
     *
     * @param array<string, WithQuery|MaybeReturnableQueryInterface> $with
     * @param bool $recursive
     *
     * @return static
     */
    public function with(array $with, bool $recursive = false): static;
    // endregion METHOD_with

    // region METHOD_addWith [DOMAIN(8): Query; CONCEPT(9): CTE; TECH(8): SQL]
    /**
     * @purpose Merge additional CTE definitions into existing ones.
     * @io array<string, WithQuery|MaybeReturnableQueryInterface> $with, bool $recursive -> static
     * @complexity 2
     *
     * @param array<string, WithQuery|MaybeReturnableQueryInterface> $with
     * @param bool $recursive
     *
     * @return static
     */
    public function addWith(array $with, bool $recursive = false): static;
    // endregion METHOD_addWith
}
// endregion INTERFACE_WithInterface
