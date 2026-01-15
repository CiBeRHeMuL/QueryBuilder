<?php

namespace AndrewGos\QueryBuilder\Expr\Window;

use AndrewGos\QueryBuilder\Expr\AbstractExpr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;

final class Over implements ExprInterface
{
    /**
     * @param ExprInterface $expr
     * @param Window|string $over window definition or window name
     */
    public function __construct(
        private ExprInterface $expr,
        private Window|string $over,
    ) {}

    /**
     * @inheritDoc
     */
    public function getExpression(GrammarInterface $grammar): string
    {
        if (is_string($this->over)) {
            return "{$this->expr->getExpression($grammar)} OVER {$grammar->escapeIdentifier($this->over)}";
        } else {
            return "{$this->expr->getExpression($grammar)} OVER {$this->over->getExpression($grammar)}";
        }
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return HExpr::mergeParams(
            $this->expr->getParams(),
            $this->over instanceof Window ? $this->over->getParams() : [],
        );
    }
}
