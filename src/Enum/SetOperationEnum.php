<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Enum;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): SetOperation; TECH(9): SQL]
/**
 * @moduleContract
 * @purpose Define SQL set operations (UNION, INTERSECT, EXCEPT) with ALL/DISTINCT modifiers.
 * @scope Set operation constants and their SQL string representations.
 * @input No runtime input — compile-time case selection.
 * @output SQL set operation clause fragment string.
 * @modulemap
 * SetOperationEnum => SQL set operations
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: Set, Operation, UNION, INTERSECT, EXCEPT, ALL, DISTINCT, SQL
// STRUCTURE: ▶ enum cases ┌Union, Intersect, Except ─● ALL|Distinct┐ → ⚡ getSql → match case → ∑ return '... ALL'|'... DISTINCT'

// region ENUM_SetOperationEnum [DOMAIN(6): Enum; CONCEPT(7): SetOperation; TECH(9): SQL]
/**
 * @purpose Represent a SQL set operation (UNION/INTERSECT/EXCEPT with ALL or DISTINCT).
 * @io self -> string SQL fragment
 */
enum SetOperationEnum
{
    case UnionAll;
    case IntersectAll;
    case ExceptAll;
    case UnionDistinct;
    case IntersectDistinct;
    case ExceptDistinct;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): SQL]
    /**
     * @purpose Convert the set operation enum to its SQL string representation.
     * @io self -> string
     * @complexity 1
     */
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
    // endregion METHOD_getSql
}
// endregion ENUM_SetOperationEnum
