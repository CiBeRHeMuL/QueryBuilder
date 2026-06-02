<?php

namespace AndrewGos\QueryBuilder\Expr\Lock\MySql;

use AndrewGos\QueryBuilder\Enum\Lock\MySql\MySqlLockModeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\MySql\MySqlLockWaitModeEnum;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Lock; CONCEPT(8): MySqlLockMode; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose Implements MySQL-specific FOR UPDATE / FOR SHARE lock modes with table lists and NOWAIT/SKIP LOCKED options.
 * @scope MySQL locking syntax generation.
 * @input MySqlLockModeEnum $mode, string[] $tables, MySqlLockWaitModeEnum $waitMode
 * @output SQL string for MySQL lock clause
 * @invariants
 * - Tables are escaped via GrammarInterface::escapeIdentifier
 * @modulemap
 * MySqlLockMode => MySQL lock mode expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MySQL, lock, FOR UPDATE, FOR SHARE, NOWAIT, SKIP LOCKED, dialect

// region CLASS_MySqlLockMode [DOMAIN(8): Lock; CONCEPT(8): MySqlLockMode; TECH(8): Dialect]
/**
 * @purpose MySQL implementation of LockModeInterface supporting lock mode, table list, and wait mode.
 */
final readonly class MySqlLockMode implements LockModeInterface
{
    /**
     * @param MySqlLockModeEnum $mode
     * @param string[] $tables
     * @param MySqlLockWaitModeEnum $waitMode
     */
    public function __construct(
        private(set) MySqlLockModeEnum $mode,
        private(set) array $tables = [],
        private(set) MySqlLockWaitModeEnum $waitMode = MySqlLockWaitModeEnum::Nowait,
    ) {}

    // region METHOD_getSql [DOMAIN(8): Lock; TECH(8): SQLGeneration]
    /**
     * @purpose Generate MySQL lock clause SQL string.
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
// endregion CLASS_MySqlLockMode
