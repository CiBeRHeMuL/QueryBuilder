<?php

namespace AndrewGos\QueryBuilder\Enum;

enum JoinTypeEnum
{
    case CrossJoin;
    case InnerJoin;
    case LeftOuterJoin;
    case RightOuterJoin;
    case FullOuterJoin;

    public function getSql(): string
    {
        return match ($this) {
            self::InnerJoin => 'INNER JOIN',
            self::LeftOuterJoin => 'LEFT JOIN',
            self::RightOuterJoin => 'RIGHT JOIN',
            self::FullOuterJoin => 'FULL JOIN',
            self::CrossJoin => 'CROSS JOIN',
        };
    }
}
