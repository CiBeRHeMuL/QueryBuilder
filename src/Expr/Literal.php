<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use UnitEnum;

class Literal extends AbstractExpr
{
    /**
     * @param bool|int|float|string|UnitEnum|null $value
     */
    public function __construct(
        private bool|int|float|string|UnitEnum|null $value,
    ) {}

    protected function doBuild(GrammarInterface $grammar): array
    {
        $paramId = $this->generateParamId();
        return [":$paramId", [$paramId => $this->value]];
    }
}
