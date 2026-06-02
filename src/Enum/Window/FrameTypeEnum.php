<?php

namespace AndrewGos\QueryBuilder\Enum\Window;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): FrameType; TECH(9): SQL]
/**
 * @moduleContract
 * @purpose Define window frame types (ROWS / RANGE / GROUPS).
 * @scope Frame type constants and their SQL string representations.
 * @input No runtime input — compile-time case selection.
 * @output SQL window frame type clause fragment string.
 * @modulemap
 * FrameTypeEnum => Window frame types
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: Window, Frame, Type, ROWS, RANGE, GROUPS, SQL

// region ENUM_FrameTypeEnum [DOMAIN(6): Enum; CONCEPT(7): FrameType; TECH(9): SQL]
/**
 * @purpose Represent a window frame type (ROWS, RANGE, or GROUPS).
 * @io self -> string SQL fragment
 */
enum FrameTypeEnum
{
    case Rows;
    case Range;
    case Groups;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): SQL]
    /**
     * @purpose Convert the frame type enum to its SQL string representation.
     * @io self -> string
     * @complexity 1
     */
    public function getSql(): string
    {
        return match ($this) {
            self::Rows => 'ROWS',
            self::Range => 'RANGE',
            self::Groups => 'GROUPS',
        };
    }
    // endregion METHOD_getSql
}
// endregion ENUM_FrameTypeEnum
