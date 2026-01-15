<?php

namespace AndrewGos\QueryBuilder\Enum\Cte;

enum SearchTypeEnum
{
    case Breadth;
    case Depth;

    public function getSql(): string
    {
        return match ($this) {
            self::Breadth => 'BREADTH',
            self::Depth => 'DEPTH',
        };
    }
}
