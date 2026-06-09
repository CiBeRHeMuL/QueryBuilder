<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Merge;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(7): ActionDelete; TECH(5): ValueObject]
/**
 * @moduleContract
 * @purpose Represents the DELETE action for WHEN MATCHED and WHEN NOT MATCHED BY SOURCE clauses. Renders as "DELETE".
 * @scope MERGE action value object.
 * @input GrammarInterface
 * @output "DELETE" SQL fragment with no params.
 * @invariants
 * - Always renders "DELETE" with empty params.
 * @modulemap
 * MergeActionDelete => MERGE DELETE action
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MergeActionDelete, DELETE, MERGE, action
// STRUCTURE: ▶ getSql → 'DELETE' + getParams → [] → ∑

// region CLASS_MergeActionDelete [DOMAIN(8): Merge; CONCEPT(7): ActionDelete; TECH(5): ValueObject]
/**
 * @purpose DELETE action for MERGE WHEN MATCHED / BY SOURCE. Renders as simple "DELETE".
 */
readonly class MergeActionDelete implements MergeWhenMatchedActionInterface
{
    // region METHOD_getSql [DOMAIN(8): Merge; CONCEPT(7): ActionDelete; TECH(5): Rendering]
    /**
     * @purpose Render as "DELETE".
     * @complexity 1
     *
     * @param GrammarInterface $grammar the grammar used for SQL rendering (unused, exists for interface compatibility)
     *
     * @return string
     */
    public function getSql(GrammarInterface $grammar): string
    {
        return 'DELETE';
    }
    // endregion METHOD_getSql

    // region METHOD_getParams [DOMAIN(8): Merge; CONCEPT(7): ActionDelete; TECH(5): Params]
    /**
     * @purpose Always returns empty array — DELETE has no params.
     * @complexity 1
     *
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }
    // endregion METHOD_getParams
}
// endregion CLASS_MergeActionDelete
