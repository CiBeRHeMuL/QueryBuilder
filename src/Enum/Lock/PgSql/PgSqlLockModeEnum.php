<?php

namespace AndrewGos\QueryBuilder\Enum\Lock\PgSql;

use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

enum PgSqlLockModeEnum implements LockModeInterface
{
    case ForUpdate;
    case ForNoKeyUpdate;
    case ForShare;
    case ForKeyShare;

    public function getSql(GrammarInterface $grammar): string
    {
        return match ($this) {
            self::ForUpdate => 'UPDATE',
            self::ForNoKeyUpdate => 'NO KEY UPDATE',
            self::ForShare => 'SHARE',
            self::ForKeyShare => 'KEY SHARE',
        };
    }
}
