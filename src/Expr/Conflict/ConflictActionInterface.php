<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Conflict;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Conflict; CONCEPT(8): Action; TECH(5): SQLStandard]
/**
 * @moduleContract
 * @purpose Defines the interface for conflict action implementations (DO NOTHING, DO UPDATE SET).
 * @scope Contract for grammar-independent conflict action SQL generation.
 * @input GrammarInterface.
 * @output SQL string + params for the conflict action clause.
 * @modulemap
 * ConflictActionInterface => Contract for conflict action SQL generation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: ON CONFLICT, action, DO NOTHING, DO UPDATE, conflict resolution
// STRUCTURE: ▶ contract methods ┌getSql(GrammarInterface), getParams()┐ → ∑ [ConflictActionInterface]

// region INTERFACE_ConflictActionInterface [DOMAIN(8): Conflict; CONCEPT(8): Action; TECH(5): SQLStandard]
/**
 * @purpose Defines the interface for conflict action implementations.
 */
interface ConflictActionInterface
{
    /**
     * @purpose Returns the SQL fragment for this conflict action.
     * @io GrammarInterface -> string
     * @complexity 1
     */
    public function getSql(GrammarInterface $grammar): string;

    /**
     * @purpose Returns the bound parameters for this conflict action.
     * @io -> array
     * @complexity 1
     */
    public function getParams(): array;
}
// endregion INTERFACE_ConflictActionInterface
