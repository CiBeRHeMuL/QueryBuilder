<?php

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

    /**
     * @param array<int|string, TSelectExpression> $columns
     *
     * @return static
     */
    public function select(array $columns): static;

    /**
     * @param array<int|string, TSelectExpression> $columns
     *
     * @return static
     */
    public function addSelect(array $columns): static;

    /**
     * @param bool $distinct
     *
     * @return static
     */
    public function distinct(bool $distinct = true): static;

    /**
     * @param TGroupExpression[] $columns
     * @param bool $distinct
     *
     * @return static
     */
    public function groupBy(array $columns, bool $distinct = false): static;

    /**
     * @param TGroupExpression[] $columns
     * @param bool $distinct
     *
     * @return static
     */
    public function addGroupBy(array $columns, bool $distinct = false): static;

    /**
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function having(array|ExprInterface $conditions): static;

    /**
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function andHaving(array|ExprInterface $conditions): static;

    /**
     * @param TConditions|ExprInterface $conditions
     *
     * @return static
     */
    public function orHaving(array|ExprInterface $conditions): static;

    /**
     * @param string $name
     * @param Window $windowDefinition
     *
     * @return static
     */
    public function window(string $name, Window $windowDefinition): static;

    /**
     * @param LockModeInterface|null $lockMode
     *
     * @return static
     */
    public function lock(?LockModeInterface $lockMode): static;
}
