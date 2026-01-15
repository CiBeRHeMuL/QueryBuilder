<?php

namespace AndrewGos\QueryBuilder\Enum\Lock;

use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

enum StandardLockModeEnum implements LockModeInterface
{
    case ForUpdate;

    public function getSql(GrammarInterface $grammar): string
    {
        return match ($this) {
            self::ForUpdate => 'UPDATE',
        };
    }
}
