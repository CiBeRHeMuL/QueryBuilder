<?php

namespace AndrewGos\QueryBuilder\Enum\Window;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): FrameBound; TECH(9): SQL]
/**
 * @moduleContract
 * @purpose Define window frame bound types (PRECEDING / FOLLOWING / CURRENT ROW).
 * @scope Frame bound constants and their SQL string representations.
 * @input No runtime input — compile-time case selection.
 * @output SQL window frame bound clause fragment string.
 * @modulemap
 * FrameBoundEnum => Window frame bound types
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: Window, Frame, Bound, PRECEDING, FOLLOWING, CURRENT ROW, SQL

// region ENUM_FrameBoundEnum [DOMAIN(6): Enum; CONCEPT(7): FrameBound; TECH(9): SQL]
/**
 * @purpose Represent a window frame bound direction (PRECEDING, FOLLOWING, or CURRENT ROW).
 * @io self -> string SQL fragment
 */
enum FrameBoundEnum
{
    case Preceding;
    case Following;
    case CurrentRow;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): SQL]
    /**
     * @purpose Convert the frame bound enum to its SQL string representation.
     * @io self -> string
     * @complexity 1
     */
    public function getSql(): string
    {
        return match ($this) {
            self::Preceding => 'PRESIDING',
            self::Following => 'FOLLOWING',
            self::CurrentRow => 'CURRENT ROW',
        };
    }
    // endregion METHOD_getSql
}
// endregion ENUM_FrameBoundEnum
