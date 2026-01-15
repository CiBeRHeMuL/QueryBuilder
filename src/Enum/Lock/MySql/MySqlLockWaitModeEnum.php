<?php

namespace AndrewGos\QueryBuilder\Enum\Lock\MySql;

enum MySqlLockWaitModeEnum
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
