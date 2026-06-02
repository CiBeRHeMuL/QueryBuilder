<?php

namespace AndrewGos\QueryBuilder\Enum;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): LimitBound; TECH(9): SQL]
/**
 * @moduleContract
 * @purpose Define LIMIT bound types for SQL query building.
 * @scope Limit bound type constants and their SQL string representations.
 * @input No runtime input — compile-time case selection.
 * @output SQL LIMIT bound clause fragment string.
 * @modulemap
 * LimitBoundTypeEnum => LIMIT bound types
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: Limit, Bound, ONLY, WITH TIES, SQL

// region ENUM_LimitBoundTypeEnum [DOMAIN(6): Enum; CONCEPT(7): LimitBound; TECH(9): SQL]
/**
 * @purpose Represent the bound type of a LIMIT clause (ONLY vs WITH TIES).
 * @io self -> string SQL fragment
 */
enum LimitBoundTypeEnum
{
    case Only;
    case WithTies;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): SQL]
    /**
     * @purpose Convert the limit bound type enum to its SQL string representation.
     * @io self -> string
     * @complexity 1
     */
    public function getSql(): string
    {
        return match ($this) {
            self::Only => 'ONLY',
            self::WithTies => 'WITH TIES',
        };
    }
    // endregion METHOD_getSql
}
// endregion ENUM_LimitBoundTypeEnum
