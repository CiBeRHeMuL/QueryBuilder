<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Conflict;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Conflict; CONCEPT(8): Target; TECH(5): SQLStandard]
/**
 * @moduleContract
 * @purpose Defines the interface for conflict target implementations ((columns) or ON CONSTRAINT name).
 * @scope Contract for grammar-independent conflict target SQL generation.
 * @input GrammarInterface.
 * @output SQL string + params for the conflict target clause.
 * @modulemap
 * ConflictTargetInterface => Contract for conflict target SQL generation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: ON CONFLICT, target, columns, constraint, conflict resolution
// STRUCTURE: ▶ contract methods ┌getSql(GrammarInterface), getParams()┐ → ∑ [ConflictTargetInterface]

// region INTERFACE_ConflictTargetInterface [DOMAIN(8): Conflict; CONCEPT(8): Target; TECH(5): SQLStandard]
/**
 * @purpose Defines the interface for conflict target implementations.
 */
interface ConflictTargetInterface
{
    /**
     * @purpose Returns the SQL fragment for this conflict target.
     * @io GrammarInterface -> string
     * @complexity 1
     */
    public function getSql(GrammarInterface $grammar): string;

    /**
     * @purpose Returns the bound parameters for this conflict target.
     * @io -> array
     * @complexity 1
     */
    public function getParams(): array;
}
// endregion INTERFACE_ConflictTargetInterface
