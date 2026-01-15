<?php

namespace AndrewGos\QueryBuilder\Enum;

enum LimitBoundTypeEnum
{
    case Only;
    case WithTies;

    public function getSql(): string
    {
        return match ($this) {
            self::Only => 'ONLY',
            self::WithTies => 'WITH TIES',
        };
    }
}
