<?php

namespace AndrewGos\QueryBuilder\Expr\Group;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use UnitEnum;

/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TGroupExpression of ExprInterface|array<TGroupExpression>
 */
class GroupingSets extends AbstractGroupingSets
{
    /**
     * @param TGroupExpression[] $columns
     */
    public function __construct(
        array $columns,
    ) {
        parent::__construct('GROUPING SETS', $columns);
    }
}
