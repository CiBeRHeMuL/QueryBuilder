<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @phpstan-template TCondition of SelectQueryInterface|UnitEnum
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of UnitEnum|array<int, bool|ExprInterface>
 */
class AndExpr extends BoolOpsExpr
{
    /**
     * @param TConditions $conditions
     */
    public function __construct(
        array $conditions,
    ) {
        parent::__construct($conditions, 'AND');
    }
}
