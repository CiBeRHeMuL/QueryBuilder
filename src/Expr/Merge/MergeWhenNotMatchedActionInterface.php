<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Merge;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(8): ActionInterface; TECH(7): TypeSafety]
/**
 * @moduleContract
 * @purpose Define the contract for actions allowed in WHEN NOT MATCHED (BY TARGET) clause only (INSERT, DO NOTHING). Kept separate from MergeWhenMatchedActionInterface for type safety.
 * @scope Type-safe interface for WHEN NOT MATCHED (BY TARGET) actions.
 * @input GrammarInterface
 * @output SQL string and params for the action.
 * @invariants
 * - All implementations must render as a valid SQL action fragment (e.g., "INSERT ...", "DO NOTHING").
 * @modulemap
 * INTERFACE MergeWhenNotMatchedActionInterface => WHEN NOT MATCHED (BY TARGET) action contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MergeWhenNotMatchedActionInterface, WHEN NOT MATCHED, merge action, INSERT, DO NOTHING
// STRUCTURE: ▶ getSql(GrammarInterface) + getParams() → ∑ [MergeWhenNotMatchedActionInterface contract]

// region INTERFACE_MergeWhenNotMatchedActionInterface [DOMAIN(8): Merge; CONCEPT(8): ActionInterface; TECH(7): TypeSafety]
/**
 * @purpose Contract for actions valid in WHEN NOT MATCHED (BY TARGET) context. Prevents UPDATE/DELETE in wrong context via type system.
 */
interface MergeWhenNotMatchedActionInterface
{
    /**
     * @purpose Render the action as a SQL fragment (e.g. "INSERT ...", "DO NOTHING").
     *
     * @param GrammarInterface $grammar the grammar used for SQL rendering and identifier escaping
     *
     * @return string rendered SQL fragment for use in MERGE clause
     */
    public function getSql(GrammarInterface $grammar): string;

    /**
     * @return array
     */
    public function getParams(): array;
}
// endregion INTERFACE_MergeWhenNotMatchedActionInterface
