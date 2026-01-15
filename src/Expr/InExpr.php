<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

class InExpr extends OpExpr
{
    /**
     * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
     * @phpstan-template TExpression of TValue|array<TExpression>
     *
     * @param TExpression $left
     * @param array<TExpression>|ExprInterface|SelectQueryInterface $right
     */
    public function __construct(
        bool|int|float|string|ExprInterface|SelectQueryInterface|array|null $left,
        array|ExprInterface|SelectQueryInterface $right,
    ) {
        parent::__construct($left, 'IN', $right);
    }
}
