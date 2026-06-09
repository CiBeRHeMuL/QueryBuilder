<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Join\JoinTable;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for SQL JOIN clause with full type support (INNER, LEFT, RIGHT, CROSS, FULL, NATURAL).
 * @scope Methods for joining tables with conditions, covering all standard join types.
 * @input Join type, table reference, conditions, optional alias.
 * @output Contract for table joining operations.
 * @modulemap
 * INTERFACE JoinInterface => JOIN clause contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: JOIN, SQL, INNER, LEFT, RIGHT, CROSS, FULL, NATURAL, tables
// STRUCTURE: ▶ ┌joinTables property┐ + join() + inner/left/right/cross/full/natural*Join() → ∑ [JoinInterface contract]

// region INTERFACE_JoinInterface [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
/**
 * This interface provides methods for working with JOIN clause.
 *
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|null
 * @template TTable of string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable
 * @template TCondition of TValue|array<TCondition>
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, TStandaloneCondition>
 *
 * @purpose Define methods for working with JOIN clause.
 */
interface JoinInterface
{
    /**
     * @var JoinTable[]
     */
    public array $joinTables {
        get;
    }

    // region METHOD_join [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create a JOIN of the specified type with conditions, optionally keyed by alias.
     * @io JoinTypeEnum $type, TTable $table, TConditions $conditions, ?string $alias -> static
     * @complexity 3
     *
     * @param JoinTypeEnum $type
     * @param TTable       $table
     * @param TConditions  $conditions
     * @param string|null  $alias
     *
     * @return static
     */
    public function join(
        JoinTypeEnum $type,
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static;
    // endregion METHOD_join

    // region METHOD_innerJoin [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create an INNER JOIN with conditions.
     * @io TTable $table, TConditions $conditions, ?string $alias -> static
     * @complexity 2
     *
     * @param TTable      $table
     * @param TConditions $conditions
     * @param string|null $alias
     *
     * @return static
     */
    public function innerJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static;
    // endregion METHOD_innerJoin

    // region METHOD_leftJoin [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create a LEFT OUTER JOIN with conditions.
     * @io TTable $table, TConditions $conditions, ?string $alias -> static
     * @complexity 2
     *
     * @param TTable      $table
     * @param TConditions $conditions
     * @param string|null $alias
     *
     * @return static
     */
    public function leftJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static;
    // endregion METHOD_leftJoin

    // region METHOD_rightJoin [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create a RIGHT OUTER JOIN with conditions.
     * @io TTable $table, TConditions $conditions, ?string $alias -> static
     * @complexity 2
     *
     * @param TTable      $table
     * @param TConditions $conditions
     * @param string|null $alias
     *
     * @return static
     */
    public function rightJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static;
    // endregion METHOD_rightJoin

    // region METHOD_crossJoin [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create a CROSS JOIN — no ON conditions per SQL standard.
     * @io TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * @param TTable      $table
     * @param string|null $alias
     *
     * @return static
     */
    public function crossJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;
    // endregion METHOD_crossJoin

    // region METHOD_fullJoin [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create a FULL OUTER JOIN with conditions.
     * @io TTable $table, TConditions $conditions, ?string $alias -> static
     * @complexity 2
     *
     * @param TTable      $table
     * @param TConditions $conditions
     * @param string|null $alias
     *
     * @return static
     */
    public function fullJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static;
    // endregion METHOD_fullJoin

    // region METHOD_naturalJoin [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create a NATURAL JOIN of the specified type (cross join is invalid for natural).
     * @io JoinTypeEnum $type, TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * @param JoinTypeEnum $type
     * @param TTable       $table
     * @param string|null  $alias
     *
     * @return static
     */
    public function naturalJoin(
        JoinTypeEnum $type,
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;
    // endregion METHOD_naturalJoin

    // region METHOD_naturalInnerJoin [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create a NATURAL INNER JOIN.
     * @io TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * @param TTable      $table
     * @param string|null $alias
     *
     * @return static
     */
    public function naturalInnerJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;
    // endregion METHOD_naturalInnerJoin

    // region METHOD_naturalLeftJoin [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create a NATURAL LEFT OUTER JOIN.
     * @io TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * @param TTable      $table
     * @param string|null $alias
     *
     * @return static
     */
    public function naturalLeftJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;
    // endregion METHOD_naturalLeftJoin

    // region METHOD_naturalRightJoin [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create a NATURAL RIGHT OUTER JOIN.
     * @io TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * @param TTable      $table
     * @param string|null $alias
     *
     * @return static
     */
    public function naturalRightJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;
    // endregion METHOD_naturalRightJoin

    // region METHOD_naturalFullJoin [DOMAIN(8): Query; CONCEPT(9): Join; TECH(8): SQL]
    /**
     * @purpose Create a NATURAL FULL OUTER JOIN.
     * @io TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * @param TTable      $table
     * @param string|null $alias
     *
     * @return static
     */
    public function naturalFullJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;
    // endregion METHOD_naturalFullJoin
}
// endregion INTERFACE_JoinInterface
