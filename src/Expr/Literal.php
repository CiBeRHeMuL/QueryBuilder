<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(6): Expression; CONCEPT(5): Literal; TECH(6): Parameterized]
/**
 * @moduleContract
 * @purpose Represents a literal value that will be bound as a query parameter.
 * @scope Parameter binding for scalar values, enums, and null.
 * @input bool|int|float|string|UnitEnum|null $value
 * @output Parameterized expression string (:paramName) and param array
 * @invariants
 * - Each Literal generates a unique param ID on build.
 * @modulemap
 * Literal => Parameterized literal value expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: Literal, parameterized value, bound parameter
// STRUCTURE: ▶ generateParamId() → ┌':paramId', [paramId => value]┐ → ∑ [Literal]

// region CLASS_Literal [DOMAIN(6): Expression; CONCEPT(5): Literal; TECH(6): Parameterized]
class Literal extends AbstractExpr
{
    /**
     * @param bool|int|float|string|\UnitEnum|null $value
     */
    public function __construct(
        private bool|int|float|string|\UnitEnum|null $value,
    ) {}

    protected function doBuild(GrammarInterface $grammar): array
    {
        $paramId = $this->generateParamId();

        return [":$paramId", [$paramId => $this->value]];
    }
}
// endregion CLASS_Literal
