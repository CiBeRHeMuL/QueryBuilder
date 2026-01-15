<?php

namespace AndrewGos\QueryBuilder\Query\Interface\PgSql;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

/**
 * This interface provides methods for working with RETURNING clause
 *
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|null
 * @template TExpression of TValue|array<TExpression>
 *
 * @template TSelectExpression of TExpression
 */
interface ReturningInterface
{
    public ?string $returningOldAlias {
        get;
    }
    public ?string $returningNewAlias {
        get;
    }
    /**
     * @var array<int|string, TSelectExpression>|null
     */
    public ?array $returningColumns {
        get;
    }

    /**
     * @param array<int|string, TSelectExpression> $columns
     * @param string|null $oldAlias
     * @param string|null $newAlias
     *
     * @return static
     */
    public function returning(array $columns, ?string $oldAlias = null, ?string $newAlias = null): static;

    /**
     * @param array<int|string, TSelectExpression> $columns
     * @param string|null $oldAlias
     * @param string|null $newAlias
     *
     * @return static
     */
    public function addReturning(array $columns, ?string $oldAlias = null, ?string $newAlias = null): static;
}
