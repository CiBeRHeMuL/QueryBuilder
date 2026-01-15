<?php

namespace AndrewGos\QueryBuilder\Enum;

enum SetOperationEnum
{
    case UnionAll;
    case IntersectAll;
    case ExceptAll;
    case UnionDistinct;
    case IntersectDistinct;
    case ExceptDistinct;

    public function getSql(): string
    {
        return match ($this) {
            self::UnionAll => 'UNION ALL',
            self::IntersectAll => 'INTERSECT ALL',
            self::ExceptAll => 'EXCEPT ALL',
            self::UnionDistinct => 'UNION DISTINCT',
            self::IntersectDistinct => 'INTERSECT DISTINCT',
            self::ExceptDistinct => 'EXCEPT DISTINCT',
        };
    }
}
