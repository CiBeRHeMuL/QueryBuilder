<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Merge;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(8): ActionInterface; TECH(7): TypeSafety]
/**
 * @moduleContract
 * @purpose Define the contract for actions allowed in WHEN MATCHED and WHEN NOT MATCHED BY SOURCE clauses (UPDATE, DELETE, DO NOTHING). NOT for WHEN NOT MATCHED (BY TARGET).
 * @scope Type-safe interface for WHEN MATCHED / BY SOURCE actions.
 * @input GrammarInterface
 * @output SQL string and params for the action.
 * @invariants
 * - All implementations must render as a valid SQL action fragment (e.g., "UPDATE SET ...", "DELETE", "DO NOTHING").
 * @modulemap
 * INTERFACE MergeWhenMatchedActionInterface => WHEN MATCHED / BY SOURCE action contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MergeWhenMatchedActionInterface, WHEN MATCHED, BY SOURCE, merge action, UPDATE, DELETE, DO NOTHING
// STRUCTURE: ▶ getSql(GrammarInterface) + getParams() → ∑ [MergeWhenMatchedActionInterface contract]

// region INTERFACE_MergeWhenMatchedActionInterface [DOMAIN(8): Merge; CONCEPT(8): ActionInterface; TECH(7): TypeSafety]
/**
 * @purpose Contract for actions valid in WHEN MATCHED and WHEN NOT MATCHED BY SOURCE context. Prevents INSERT in wrong context via type system.
 */
interface MergeWhenMatchedActionInterface
{
    /**
     * @purpose Render the action as a SQL fragment (e.g. "UPDATE SET ...", "DELETE", "DO NOTHING").
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
// endregion INTERFACE_MergeWhenMatchedActionInterface
