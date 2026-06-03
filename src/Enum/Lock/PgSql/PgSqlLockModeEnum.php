<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Enum\Lock\PgSql;

use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): LockMode; TECH(9): PgSQL]
/**
 * @moduleContract
 * @purpose Define PostgreSQL-specific lock modes (FOR UPDATE / FOR NO KEY UPDATE / FOR SHARE / FOR KEY SHARE).
 * @scope PostgreSQL lock mode constants with grammar-aware SQL generation.
 * @input GrammarInterface for dialect-specific formatting.
 * @output SQL lock mode clause fragment string.
 * @modulemap
 * PgSqlLockModeEnum => PostgreSQL lock modes
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, Lock, Mode, FOR UPDATE, FOR SHARE, FOR NO KEY UPDATE, FOR KEY SHARE
// STRUCTURE: ▶ enum cases ┌ForUpdate, ForNoKeyUpdate, ForShare, ForKeyShare┐ → ⚡ getSql(grammar) → match case → ∑ return 'UPDATE'|'NO KEY UPDATE'|'SHARE'|'KEY SHARE'

// region ENUM_PgSqlLockModeEnum [DOMAIN(6): Enum; CONCEPT(7): LockMode; TECH(9): PgSQL]
/**
 * @purpose Represent a PostgreSQL-specific lock mode.
 * @io self, GrammarInterface -> string SQL fragment
 */
enum PgSqlLockModeEnum implements LockModeInterface
{
    case ForUpdate;
    case ForNoKeyUpdate;
    case ForShare;
    case ForKeyShare;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): PgSQL]
    /**
     * @purpose Convert the PostgreSQL lock mode enum to its SQL string representation.
     * @io self, GrammarInterface -> string
     * @complexity 1
     */
    public function getSql(GrammarInterface $grammar): string
    {
        return match ($this) {
            self::ForUpdate => 'UPDATE',
            self::ForNoKeyUpdate => 'NO KEY UPDATE',
            self::ForShare => 'SHARE',
            self::ForKeyShare => 'KEY SHARE',
        };
    }
    // endregion METHOD_getSql
}
// endregion ENUM_PgSqlLockModeEnum
