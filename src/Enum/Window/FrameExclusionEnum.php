<?php

namespace AndrewGos\QueryBuilder\Enum\Window;

enum FrameExclusionEnum
{
    case CurrentRow;
    case Group;
    case Ties;
    case NoOthers;

    public function getSql(): string
    {
        return match ($this) {
            self::CurrentRow => 'CURRENT ROW',
            self::Group => 'GROUP',
            self::Ties => 'TIES',
            self::NoOthers => 'NO OTHERS',
        };
    }
}
