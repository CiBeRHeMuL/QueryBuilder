<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Conflict\PgSql;

use AndrewGos\QueryBuilder\Expr\Conflict\ConflictTargetInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Conflict; CONCEPT(8): TargetConstraint; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL conflict target by constraint name: ON CONSTRAINT constraint_name.
 * @scope ON CONFLICT ON CONSTRAINT clause target generation.
 * @input string $constraintName.
 * @output SQL string for constraint-based conflict target.
 * @modulemap
 * PgSqlConflictTargetConstraint => Constraint-based conflict target
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, ON CONFLICT, ON CONSTRAINT, conflict target
// STRUCTURE: ▶ getSql ┌grammar┐ → ⚡ return 'ON CONSTRAINT ' . escapeIdentifier → getParams → ⚡ return [] → ∑ [PgSqlConflictTargetConstraint]

// region CLASS_PgSqlConflictTargetConstraint [DOMAIN(8): Conflict; CONCEPT(8): TargetConstraint; TECH(8): Dialect]
/**
 * @purpose PostgreSQL implementation of ConflictTargetInterface — renders ON CONSTRAINT "constraint_name".
 */
final readonly class PgSqlConflictTargetConstraint implements ConflictTargetInterface
{
    public function __construct(
        private(set) string $constraintName,
    ) {}

    // region METHOD_getSql [DOMAIN(8): Conflict; TECH(8): SQLGeneration]
    /**
     * @purpose Render the ON CONSTRAINT conflict target SQL.
     */
    public function getSql(GrammarInterface $grammar): string
    {
        return 'ON CONSTRAINT ' . $grammar->escapeIdentifier($this->constraintName);
    }
    // endregion METHOD_getSql

    // region METHOD_getParams [DOMAIN(8): Conflict; TECH(8): ParameterExtraction]
    /**
     * @purpose No params for constraint target.
     */
    public function getParams(): array
    {
        return [];
    }
    // endregion METHOD_getParams
}
// endregion CLASS_PgSqlConflictTargetConstraint
