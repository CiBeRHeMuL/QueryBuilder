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
abstract class AbstractGroupingSets extends AbstractExpr
{
    /**
     * @param TGroupExpression[] $columns
     */
    public function __construct(
        private string $prefix,
        private array $columns,
    ) {
        HExpr::testGroupByExpr($this->columns);
    }

    protected function doBuild(GrammarInterface $grammar): array
    {
        $parts = [];
        $params = [];
        $vb = new ValueBuilder();
        foreach ($this->columns as $column) {
            $expr = $vb->build($column, $grammar, true);
            $parts[] = $expr->getExpression($grammar);
            $params = HExpr::mergeParams($params, $expr->getParams());
        }

        return [$this->prefix . ' (' . implode(', ', $parts) . ')', $params];
    }
}
