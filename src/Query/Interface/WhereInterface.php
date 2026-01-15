<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

/**
 * This interface provides methods for working with WHERE clause
 *
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 *
 * @template TCondition of TValue|array<TCondition>
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, TStandaloneCondition>
 */
interface WhereInterface
{
    /**
     * @var TConditions
     */
    public array $where {
        get;
    }

    /**
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function where(array|ExprInterface $conditions): static;

    /**
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function andWhere(array|ExprInterface $conditions): static;

    /**
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function orWhere(array|ExprInterface $conditions): static;
}
