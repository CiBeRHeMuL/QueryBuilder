<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Expression; CONCEPT(8): Contract; TECH(8): Interface]
/**
 * @moduleContract
 * @purpose Defines the fundamental contract for all SQL expression nodes in the query builder.
 * @scope Expression rendering, parameter extraction.
 * @input GrammarInterface
 * @output string (SQL fragment), array (params)
 * @invariants
 * - getExpression() must be called before getParams() for lazy-building implementations.
 * @modulemap
 * ExprInterface => Base contract for expression nodes
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: ExprInterface, expression contract, SQL fragment
// STRUCTURE: ▶ getExpression(GrammarInterface) + getParams() → ∑ [ExprInterface contract]

// region INTERFACE_ExprInterface [DOMAIN(8): Expression; CONCEPT(8): Contract; TECH(8): Interface]
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
// endregion INTERFACE_ExprInterface
