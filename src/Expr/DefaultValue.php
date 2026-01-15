<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

/**
 * This expression can be used to specify that value in INSERT statement should be set to DEFAULT value
 */
class DefaultValue implements ExprInterface
{
    /**
     * @inheritDoc
     */
    public function getExpression(GrammarInterface $grammar): string
    {
        return 'DEFAULT';
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return [];
    }
}
