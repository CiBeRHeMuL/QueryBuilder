<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Enum\Cte;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): CTESearch; TECH(9): SQL]
/**
 * @moduleContract
 * @purpose Define CTE search type (BREADTH / DEPTH) for recursive CTE queries.
 * @scope Search type constants and their SQL string representations.
 * @input No runtime input — compile-time case selection.
 * @output SQL SEARCH clause fragment string.
 * @modulemap
 * SearchTypeEnum => CTE search types
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: CTE, Search, BREADTH, DEPTH, Recursive, SQL
// STRUCTURE: ▶ enum cases ┌Breadth, Depth┐ → ⚡ getSql → match case → ∑ return 'BREADTH'|'DEPTH'

// region ENUM_SearchTypeEnum [DOMAIN(6): Enum; CONCEPT(7): CTESearch; TECH(9): SQL]
/**
 * @purpose Represent search type for recursive CTE (BREADTH or DEPTH first).
 * @io self -> string SQL fragment
 */
enum SearchTypeEnum
{
    case Breadth;
    case Depth;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): SQL]
    /**
     * @purpose Convert the search type enum to its SQL string representation.
     * @io self -> string
     * @complexity 1
     */
    public function getSql(): string
    {
        return match ($this) {
            self::Breadth => 'BREADTH',
            self::Depth => 'DEPTH',
        };
    }
    // endregion METHOD_getSql
}
// endregion ENUM_SearchTypeEnum
