<?php

namespace AndrewGos\QueryBuilder\Enum;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): JoinType; TECH(9): SQL]
/**
 * @moduleContract
 * @purpose Define supported SQL JOIN types for query building.
 * @scope Join type constants and their SQL string representations.
 * @input No runtime input — compile-time case selection.
 * @output SQL JOIN clause fragment string.
 * @modulemap
 * JoinTypeEnum => SQL JOIN types
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: Join, SQL, JOIN, INNER, OUTER, CROSS, LEFT, RIGHT, FULL

// region ENUM_JoinTypeEnum [DOMAIN(6): Enum; CONCEPT(7): JoinType; TECH(9): SQL]
/**
 * @purpose Represent the type of JOIN in a SQL query.
 * @io self -> string SQL fragment
 */
enum JoinTypeEnum
{
    case CrossJoin;
    case InnerJoin;
    case LeftOuterJoin;
    case RightOuterJoin;
    case FullOuterJoin;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): SQL]
    /**
     * @purpose Convert the join type enum to its SQL string representation.
     * @io self -> string
     * @complexity 1
     */
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
    // endregion METHOD_getSql
}
// endregion ENUM_JoinTypeEnum
