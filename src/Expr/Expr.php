<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(7): Expression; CONCEPT(6): SimpleExpr; TECH(7): DTO]
/**
 * @moduleContract
 * @purpose A simple immutable expression node holding a pre-built SQL fragment and its params.
 * @scope Direct SQL expression values, parameterized fragments.
 * @input string $expression, array $params
 * @output string (SQL fragment), array (params)
 * @invariants
 * - Once constructed, the expression string and params are immutable.
 * @modulemap
 * Expr => Simple immutable expression node
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: Expr, simple expression, immutable, SQL fragment
// STRUCTURE: ▶ __construct ┌expression, params┐ → getExpression → ⊕ return expression → getParams → ⊕ return params → ∑ [Expr immutable node]

// region CLASS_Expr [DOMAIN(7): Expression; CONCEPT(6): SimpleExpr; TECH(7): DTO]
class Expr implements ExprInterface
{
    /**
     * @template TParam of null|bool|int|float|string|UnitEnum
     *
     * @param string   $expression
     * @param TParam[] $params
     */
    public function __construct(
        private(set) string $expression,
        private(set) array $params = [],
    ) {}

    /**
     * {@inheritDoc}
     */
    public function getExpression(GrammarInterface $grammar): string
    {
        return $this->expression;
    }

    /**
     * {@inheritDoc}
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
// endregion CLASS_Expr
