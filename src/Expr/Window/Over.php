<?php

namespace AndrewGos\QueryBuilder\Expr\Window;

use AndrewGos\QueryBuilder\Expr\AbstractExpr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;

// region MODULE_CONTRACT [DOMAIN(8): Window; CONCEPT(8): OverClause; TECH(6): SQLAnalytics]
/**
 * @moduleContract
 * @purpose Represents an OVER clause for window functions, referencing either a named window or inline window definition.
 * @scope Wraps a function expression with a window reference (name or inline definition).
 * @input Function expression and window reference (Window object or window name string).
 * @output Rendered SQL fragment for OVER clause.
 * @modulemap
 * Over => OVER clause for window functions
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: window function, OVER clause, analytics, SQL, PostgreSQL, MySQL, SQL Server
// STRUCTURE: ▶ Init ┌fn_expr + over_ref┐ → ◇ 〈is_string(over) ?〉 → A: "fn OVER name" or B: "fn OVER (window_def)" → ⊕ getParams from both → ∑ return string + params

// region CLASS_Over [DOMAIN(8): Window; CONCEPT(8): OverClause; TECH(6): SQLAnalytics]
/**
 * @purpose Represents an OVER clause for window functions.
 */
final class Over implements ExprInterface
{
    // region METHOD___construct [DOMAIN(8): Window; CONCEPT(5): Ctor; TECH(5): DI]
    /**
     * @purpose Initializes the OVER clause with a function expression and window reference.
     * @param ExprInterface $expr
     * @param Window|string $over window definition or window name
     * @io ExprInterface, Window|string -> void
     * @complexity 1
     */
    public function __construct(
        private ExprInterface $expr,
        private Window|string $over,
    ) {}
    // endregion METHOD___construct

    // region METHOD_getExpression [DOMAIN(8): Window; CONCEPT(8): SQLRender; TECH(6): GrammarInterface]
    /**
     * @purpose Renders the OVER clause SQL, resolving named window or inline window definition.
     * @inheritDoc
     * @io GrammarInterface -> string
     * @complexity 3
     */
    public function getExpression(GrammarInterface $grammar): string
    {
        if (is_string($this->over)) {
            return "{$this->expr->getExpression($grammar)} OVER {$grammar->escapeIdentifier($this->over)}";
        } else {
            return "{$this->expr->getExpression($grammar)} OVER {$this->over->getExpression($grammar)}";
        }
    }
    // endregion METHOD_getExpression

    // region METHOD_getParams [DOMAIN(8): Window; CONCEPT(6): Params; TECH(5): Merge]
    /**
     * @purpose Merges parameters from both the function expression and window definition.
     * @inheritDoc
     * @io void -> array
     * @complexity 2
     */
    public function getParams(): array
    {
        return HExpr::mergeParams(
            $this->expr->getParams(),
            $this->over instanceof Window ? $this->over->getParams() : [],
        );
    }
    // endregion METHOD_getParams
}
// endregion CLASS_Over
