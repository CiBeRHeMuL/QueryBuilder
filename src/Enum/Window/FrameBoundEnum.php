<?php

namespace AndrewGos\QueryBuilder\Enum\Window;

enum FrameBoundEnum
{
    case Preceding;
    case Following;
    case CurrentRow;

    public function getSql(): string
    {
        return match ($this) {
            self::Preceding => 'PRESIDING',
            self::Following => 'FOLLOWING',
            self::CurrentRow => 'CURRENT ROW',
        };
    }
}
