<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Join\JoinTable;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

/**
 * This interface provides methods for working with JOIN clause
 *
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|null
 *
 * @template TTable of string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable
 *
 * @template TCondition of TValue|array<TCondition>
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, TStandaloneCondition>
 */
interface JoinInterface
{
    /**
     * @var JoinTable[]
     */
    public array $joinTables {
        get;
    }

    /**
     * @param JoinTypeEnum $type
     * @param TTable $table
     * @param TConditions $conditions
     * @param string|null $alias
     *
     * @return static
     */
    public function join(
        JoinTypeEnum $type,
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static;

    /**
     * @param TTable $table
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

    /**
     * @param TTable $table
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

    /**
     * @param TTable $table
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

    /**
     * @param TTable $table
     * @param TConditions $conditions
     * @param string|null $alias
     *
     * @return static
     */
    public function crossJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static;

    /**
     * @param TTable $table
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

    /**
     * @param JoinTypeEnum $type
     * @param TTable $table
     * @param string|null $alias
     *
     * @return static
     */
    public function naturalJoin(
        JoinTypeEnum $type,
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;

    /**
     * @param TTable $table
     * @param string|null $alias
     *
     * @return static
     */
    public function naturalInnerJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;

    /**
     * @param TTable $table
     * @param string|null $alias
     *
     * @return static
     */
    public function naturalLeftJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;

    /**
     * @param TTable $table
     * @param string|null $alias
     *
     * @return static
     */
    public function naturalRightJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;

    /**
     * @param TTable $table
     * @param string|null $alias
     *
     * @return static
     */
    public function naturalFullJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static;
}
