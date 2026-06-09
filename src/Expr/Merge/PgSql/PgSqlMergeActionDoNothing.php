<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Merge\PgSql;

use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenMatchedActionInterface;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenNotMatchedActionInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(7): ActionDoNothing; TECH(6): PgSql]
/**
 * @moduleContract
 * @purpose Represents the DO NOTHING action for PostgreSQL MERGE. Implements both action interfaces, allowing it to be used in WHEN MATCHED, WHEN NOT MATCHED, and WHEN NOT MATCHED BY SOURCE.
 * @scope PostgreSQL MERGE action value object.
 * @input GrammarInterface
 * @output "DO NOTHING" SQL fragment with no params.
 * @invariants
 * - Implements both MergeWhenMatchedActionInterface and MergeWhenNotMatchedActionInterface for cross-context compatibility.
 * @modulemap
 * PgSqlMergeActionDoNothing => PostgreSQL MERGE DO NOTHING action
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PgSqlMergeActionDoNothing, DO NOTHING, MERGE, PostgreSQL, action
// STRUCTURE: ▶ getSql → 'DO NOTHING' + getParams → [] → ∑

// region CLASS_PgSqlMergeActionDoNothing [DOMAIN(8): Merge; CONCEPT(7): ActionDoNothing; TECH(6): PgSql]
/**
 * @purpose PostgreSQL-specific DO NOTHING action for MERGE. Works in all three clause types due to dual interface implementation.
 */
readonly class PgSqlMergeActionDoNothing implements MergeWhenMatchedActionInterface, MergeWhenNotMatchedActionInterface
{
    // region METHOD_getSql [DOMAIN(8): Merge; CONCEPT(7): ActionDoNothing; TECH(5): Rendering]
    /**
     * @purpose Render as "DO NOTHING".
     * @complexity 1
     *
     * @param GrammarInterface $grammar the grammar used for SQL rendering (unused, exists for interface compatibility)
     *
     * @return string
     */
    public function getSql(GrammarInterface $grammar): string
    {
        return 'DO NOTHING';
    }
    // endregion METHOD_getSql

    // region METHOD_getParams [DOMAIN(8): Merge; CONCEPT(7): ActionDoNothing; TECH(5): Params]
    /**
     * @purpose Always returns empty array — DO NOTHING has no params.
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
// endregion CLASS_PgSqlMergeActionDoNothing
