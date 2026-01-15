<?php

namespace AndrewGos\QueryBuilder\Expr\Group;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Expr\AbstractExpr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use UnitEnum;

/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TGroupExpression of ExprInterface|array<TGroupExpression>
 */
class Cube extends AbstractGroupingSets
{
    /**
     * @param TGroupExpression[] $columns
     */
    public function __construct(
        array $columns,
    ) {
        parent::__construct('CUBE', $columns);
    }
}
