<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use UnitEnum;

interface ExprInterface
{
    /**
     * @param GrammarInterface $grammar
     *
     * @return string
     */
    public function getExpression(GrammarInterface $grammar): string;

    /**
     * @template TParam of null|bool|int|float|string|UnitEnum
     *
     * @return array<string, TParam>
     */
    public function getParams(): array;
}
