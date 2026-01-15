<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use UnitEnum;

final class Expr implements ExprInterface
{
    /**
     * @template TParam of null|bool|int|float|string|UnitEnum
     *
     * @param string $expression
     * @param TParam[] $params
     */
    public function __construct(
        private(set) string $expression,
        private(set) array $params = [],
    ) {}

    /**
     * @inheritDoc
     */
    public function getExpression(GrammarInterface $grammar): string
    {
        return $this->expression;
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
