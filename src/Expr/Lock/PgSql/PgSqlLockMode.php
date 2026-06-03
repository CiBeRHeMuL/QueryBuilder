<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Lock\PgSql;

use AndrewGos\QueryBuilder\Enum\Lock\PgSql\PgSqlLockModeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\PgSql\PgSqlLockWaitModeEnum;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Lock; CONCEPT(8): PgSqlLockMode; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose Implements PostgreSQL-specific FOR UPDATE / FOR NO KEY UPDATE / FOR SHARE / FOR KEY SHARE lock modes.
 * @scope PostgreSQL locking syntax generation with table lists and wait modes.
 * @input PgSqlLockModeEnum $mode, string[] $tables, PgSqlLockWaitModeEnum $waitMode
 * @output SQL string for PostgreSQL lock clause
 * @invariants
 * - Tables are escaped via GrammarInterface::escapeIdentifier
 * @modulemap
 * PgSqlLockMode => PostgreSQL lock mode expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, lock, FOR UPDATE, FOR NO KEY UPDATE, FOR SHARE, FOR KEY SHARE, dialect
// STRUCTURE: ⚡ [mode, tables, waitMode] → ┌modeSql┐ → ◇ tables? → ⊕ 'OF' + escapeIdentifier(each) → ┌waitModeSql┐ → ∑ implode(' ')

// region CLASS_PgSqlLockMode [DOMAIN(8): Lock; CONCEPT(8): PgSqlLockMode; TECH(8): Dialect]
/**
 * @purpose PostgreSQL implementation of LockModeInterface supporting various lock strengths and table targeting.
 */
final readonly class PgSqlLockMode implements LockModeInterface
{
    /**
     * @param PgSqlLockModeEnum $mode
     * @param string[] $tables
     * @param PgSqlLockWaitModeEnum $waitMode
     */
    public function __construct(
        private(set) PgSqlLockModeEnum $mode,
        private(set) array $tables = [],
        private(set) PgSqlLockWaitModeEnum $waitMode = PgSqlLockWaitModeEnum::Nowait,
    ) {}

    // region METHOD_getSql [DOMAIN(8): Lock; TECH(8): SQLGeneration]
    /**
     * @purpose Generate PostgreSQL lock clause SQL string.
     */
    public function getSql(GrammarInterface $grammar): string
    {
        $parts = [$this->mode->getSql($grammar)];

        if ($this->tables) {
            $parts[] = 'OF';
            foreach ($this->tables as $table) {
                $parts[] = $grammar->escapeIdentifier($table);
            }
        }

        $parts[] = $this->waitMode->getSql();

        return implode(' ', $parts);
    }
    // endregion METHOD_getSql
}
// endregion CLASS_PgSqlLockMode
