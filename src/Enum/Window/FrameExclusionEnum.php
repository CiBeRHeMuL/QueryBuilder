<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Enum\Window;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): FrameExclusion; TECH(9): SQL]
/**
 * @moduleContract
 * @purpose Define window frame exclusion types (CURRENT ROW / GROUP / TIES / NO OTHERS).
 * @scope Frame exclusion constants and their SQL string representations.
 * @input No runtime input — compile-time case selection.
 * @output SQL window frame exclusion clause fragment string.
 * @modulemap
 * FrameExclusionEnum => Window frame exclusion types
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: Window, Frame, Exclusion, CURRENT ROW, GROUP, TIES, NO OTHERS, SQL
// STRUCTURE: ▶ enum cases ┌CurrentRow, Group, Ties, NoOthers┐ → ⚡ getSql → match case → ∑ return 'CURRENT ROW'|'GROUP'|'TIES'|'NO OTHERS'

// region ENUM_FrameExclusionEnum [DOMAIN(6): Enum; CONCEPT(7): FrameExclusion; TECH(9): SQL]
/**
 * @purpose Represent a window frame exclusion mode.
 * @io self -> string SQL fragment
 */
enum FrameExclusionEnum
{
    case CurrentRow;
    case Group;
    case Ties;
    case NoOthers;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): SQL]
    /**
     * @purpose Convert the frame exclusion enum to its SQL string representation.
     * @io self -> string
     * @complexity 1
     */
    public function getSql(): string
    {
        return match ($this) {
            self::CurrentRow => 'CURRENT ROW',
            self::Group => 'GROUP',
            self::Ties => 'TIES',
            self::NoOthers => 'NO OTHERS',
        };
    }
    // endregion METHOD_getSql
}
// endregion ENUM_FrameExclusionEnum
