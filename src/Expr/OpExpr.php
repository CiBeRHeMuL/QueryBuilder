<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use UnitEnum;

class OpExpr extends AbstractExpr
{
    /**
     * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
     * @phpstan-template TExpression of TValue|array<TExpression>
     *
     * @param TExpression $left
     * @param string $operator
     * @param TExpression $right
     */
    public function __construct(
        private bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|array|null $left,
        private string $operator,
        private bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|array|null $right,
    ) {
        // Change operator to IS if right expr is null, true or false (to prevent a = null expressions).
        // TODO am i need to do it here??
        if ($this->operator === '=' && in_array($this->right, [null, true, false], true)) {
            $this->operator = 'IS';
        }
    }

    protected function doBuild(GrammarInterface $grammar): array
    {
        $vb = new ValueBuilder();
        $left = $vb->build($this->left, $grammar);
        $right = $vb->build($this->right, $grammar);

        $shouldParenthesizeLeft = $this->left instanceof ExprInterface;
        $shouldParenthesizeRight = $this->right instanceof ExprInterface;

        $expr = sprintf(
            '%s%s%s %s %s%s%s',
            $shouldParenthesizeLeft ? '(' : '',
            $left->getExpression($grammar),
            $shouldParenthesizeLeft ? ')' : '',
            $this->operator,
            $shouldParenthesizeRight ? '(' : '',
            $right->getExpression($grammar),
            $shouldParenthesizeRight ? ')' : '',
        );
        $params ??= array_merge(
            $left->getParams(),
            $right->getParams(),
        );

        return [$expr, $params];
    }
}
