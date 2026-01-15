<?php

namespace AndrewGos\QueryBuilder\Expr\Lock\PgSql;

use AndrewGos\QueryBuilder\Enum\Lock\PgSql\PgSqlLockModeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\PgSql\PgSqlLockWaitModeEnum;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

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
}
