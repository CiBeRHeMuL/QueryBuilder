<?php

namespace AndrewGos\QueryBuilder\Enum\Lock\MySql;

use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): LockMode; TECH(9): MySQL]
/**
 * @moduleContract
 * @purpose Define MySQL-specific lock modes (FOR UPDATE / FOR SHARE).
 * @scope MySQL lock mode constants with grammar-aware SQL generation.
 * @input GrammarInterface for dialect-specific formatting.
 * @output SQL lock mode clause fragment string.
 * @modulemap
 * MySqlLockModeEnum => MySQL lock modes
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MySQL, Lock, Mode, FOR UPDATE, FOR SHARE

// region ENUM_MySqlLockModeEnum [DOMAIN(6): Enum; CONCEPT(7): LockMode; TECH(9): MySQL]
/**
 * @purpose Represent a MySQL-specific lock mode (FOR UPDATE or FOR SHARE).
 * @io self, GrammarInterface -> string SQL fragment
 */
enum MySqlLockModeEnum implements LockModeInterface
{
    case ForUpdate;
    case ForShare;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): MySQL]
    /**
     * @purpose Convert the MySQL lock mode enum to its SQL string representation.
     * @io self, GrammarInterface -> string
     * @complexity 1
     */
    public function getSql(GrammarInterface $grammar): string
    {
        return match ($this) {
            self::ForUpdate => 'UPDATE',
            self::ForShare => 'SHARE',
        };
    }
    // endregion METHOD_getSql
}
// endregion ENUM_MySqlLockModeEnum
