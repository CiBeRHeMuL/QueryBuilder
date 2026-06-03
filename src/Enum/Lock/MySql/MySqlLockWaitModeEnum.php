<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Enum\Lock\MySql;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): LockWaitMode; TECH(9): MySQL]
/**
 * @moduleContract
 * @purpose Define MySQL lock wait modes (NOWAIT / SKIP LOCKED).
 * @scope Lock wait mode constants and their SQL string representations.
 * @input No runtime input — compile-time case selection.
 * @output SQL lock wait mode clause fragment string.
 * @modulemap
 * MySqlLockWaitModeEnum => MySQL lock wait modes
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MySQL, Lock, Wait, NOWAIT, SKIP LOCKED
// STRUCTURE: ▶ enum cases ┌Nowait, SkipLocked┐ → ⚡ getSql → match case → ∑ return 'NOWAIT'|'SKIP LOCKED'

// region ENUM_MySqlLockWaitModeEnum [DOMAIN(6): Enum; CONCEPT(7): LockWaitMode; TECH(9): MySQL]
/**
 * @purpose Represent a MySQL lock wait mode (NOWAIT or SKIP LOCKED).
 * @io self -> string SQL fragment
 */
enum MySqlLockWaitModeEnum
{
    case Nowait;
    case SkipLocked;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): MySQL]
    /**
     * @purpose Convert the MySQL lock wait mode enum to its SQL string representation.
     * @io self -> string
     * @complexity 1
     */
    public function getSql(): string
    {
        return match ($this) {
            self::Nowait => 'NOWAIT',
            self::SkipLocked => 'SKIP LOCKED',
        };
    }
    // endregion METHOD_getSql
}
// endregion ENUM_MySqlLockWaitModeEnum
