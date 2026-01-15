<?php

namespace AndrewGos\QueryBuilder\Enum\Window;

enum FrameTypeEnum
{
    case Rows;
    case Range;
    case Groups;

    public function getSql(): string
    {
        return match ($this) {
            self::Rows => 'ROWS',
            self::Range => 'RANGE',
            self::Groups => 'GROUPS',
        };
    }
}
