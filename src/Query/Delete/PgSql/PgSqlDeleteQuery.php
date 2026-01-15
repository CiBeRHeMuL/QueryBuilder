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
use AndrewGos\QueryBuilder\Query\Trait\PgSql\UsingTrait;
use UnitEnum;

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
 */
class PgSqlDeleteQuery extends DeleteQuery implements JoinInterface, ReturningInterface, MaybeReturnableQueryInterface
{
    use ReturningTrait;
    use JoinTrait;

    /**
     * @var array<int|string, TNormalizedTable>
     */
    protected(set) array $using = [];

    /**
     * @param array<int|string, TTable> $tables
     *
     * @return static
     */
    public function using(array $tables): static
    {
        $this->using = array_map(HExpr::normalizeTable(...), $tables);

        return $this;
    }

    /**
     * @param array<int|string, TTable> $tables
     *
     * @return static
     */
    public function addUsing(array $tables): static
    {
        $this->using = array_merge(
            $this->using,
            array_map(HExpr::normalizeTable(...), $tables),
        );
        return $this;
    }

    /**
     * For PostgreSQL we can use result of DELETE statement only if RETURNING clause is specified
     *
     * @return bool
     */
    public function isReturnable(): bool
    {
        return $this->returningColumns !== null;
    }
}
