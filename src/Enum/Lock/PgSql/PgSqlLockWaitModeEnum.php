<?php

namespace AndrewGos\QueryBuilder\Enum\Lock\PgSql;

enum PgSqlLockWaitModeEnum
{
    case Nowait;
    case SkipLocked;

    public function getSql(): string
    {
        return match ($this) {
            self::Nowait => 'NOWAIT',
            self::SkipLocked => 'SKIP LOCKED',
        };
    }
}
