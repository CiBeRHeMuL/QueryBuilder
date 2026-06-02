<?php

namespace AndrewGos\QueryBuilder\Enum\Lock\PgSql;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): LockWaitMode; TECH(9): PgSQL]
/**
 * @moduleContract
 * @purpose Define PostgreSQL lock wait modes (NOWAIT / SKIP LOCKED).
 * @scope Lock wait mode constants and their SQL string representations.
 * @input No runtime input — compile-time case selection.
 * @output SQL lock wait mode clause fragment string.
 * @modulemap
 * PgSqlLockWaitModeEnum => PostgreSQL lock wait modes
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, Lock, Wait, NOWAIT, SKIP LOCKED

// region ENUM_PgSqlLockWaitModeEnum [DOMAIN(6): Enum; CONCEPT(7): LockWaitMode; TECH(9): PgSQL]
/**
 * @purpose Represent a PostgreSQL lock wait mode (NOWAIT or SKIP LOCKED).
 * @io self -> string SQL fragment
 */
enum PgSqlLockWaitModeEnum
{
    case Nowait;
    case SkipLocked;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): PgSQL]
    /**
     * @purpose Convert the PostgreSQL lock wait mode enum to its SQL string representation.
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
// endregion ENUM_PgSqlLockWaitModeEnum
