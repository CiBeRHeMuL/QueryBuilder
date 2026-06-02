<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Conflict\PgSql;

use AndrewGos\QueryBuilder\Expr\Conflict\ConflictActionInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Conflict; CONCEPT(8): ActionDoNothing; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL conflict action DO NOTHING — renders "DO NOTHING".
 * @scope ON CONFLICT DO NOTHING clause action generation.
 * @input No input.
 * @output SQL string "DO NOTHING".
 * @modulemap
 * PgSqlConflictActionDoNothing => DO NOTHING conflict action
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, ON CONFLICT, DO NOTHING, conflict action

// region CLASS_PgSqlConflictActionDoNothing [DOMAIN(8): Conflict; CONCEPT(8): ActionDoNothing; TECH(8): Dialect]
/**
 * @purpose PostgreSQL implementation of ConflictActionInterface — renders DO NOTHING.
 */
final readonly class PgSqlConflictActionDoNothing implements ConflictActionInterface
{
    // region METHOD_getSql [DOMAIN(8): Conflict; TECH(8): SQLGeneration]
    /**
     * @purpose Return "DO NOTHING" SQL fragment.
     */
    public function getSql(GrammarInterface $grammar): string
    {
        return 'DO NOTHING';
    }
    // endregion METHOD_getSql

    // region METHOD_getParams [DOMAIN(8): Conflict; TECH(8): ParameterExtraction]
    /**
     * @purpose No params for DO NOTHING.
     */
    public function getParams(): array
    {
        return [];
    }
    // endregion METHOD_getParams
}
// endregion CLASS_PgSqlConflictActionDoNothing
