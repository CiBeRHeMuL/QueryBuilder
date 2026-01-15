<?php

namespace AndrewGos\QueryBuilder\Enum\Lock\MySql;

use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

enum MySqlLockModeEnum implements LockModeInterface
{
    case ForUpdate;
    case ForShare;

    public function getSql(GrammarInterface $grammar): string
    {
        return match ($this) {
            self::ForUpdate => 'UPDATE',
            self::ForShare => 'SHARE',
        };
    }
}
