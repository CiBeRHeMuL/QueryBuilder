<?php

namespace AndrewGos\QueryBuilder\Expr\Lock\MySql;

use AndrewGos\QueryBuilder\Enum\Lock\MySql\MySqlLockModeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\MySql\MySqlLockWaitModeEnum;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

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
