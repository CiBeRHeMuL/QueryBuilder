<?php

namespace AndrewGos\QueryBuilder\Enum\Lock;

use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): LockMode; TECH(9): SQL]
/**
 * @moduleContract
 * @purpose Define standard SQL lock mode (FOR UPDATE).
 * @scope Lock mode constants with grammar-aware SQL generation.
 * @input GrammarInterface for dialect-specific formatting.
 * @output SQL lock mode clause fragment string.
 * @modulemap
 * StandardLockModeEnum => Standard SQL lock modes
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: Lock, Mode, FOR UPDATE, Standard, SQL

// region ENUM_StandardLockModeEnum [DOMAIN(6): Enum; CONCEPT(7): LockMode; TECH(9): SQL]
/**
 * @purpose Represent a standard SQL lock mode (FOR UPDATE).
 * @io self, GrammarInterface -> string SQL fragment
 */
enum StandardLockModeEnum implements LockModeInterface
{
    case ForUpdate;

    // region METHOD_getSql [DOMAIN(6): Enum; CONCEPT(5): StringConversion; TECH(9): SQL]
    /**
     * @purpose Convert the lock mode enum to its SQL string representation.
     * @io self, GrammarInterface -> string
     * @complexity 1
     */
    public function getSql(GrammarInterface $grammar): string
    {
        return match ($this) {
            self::ForUpdate => 'UPDATE',
        };
    }
    // endregion METHOD_getSql
}
// endregion ENUM_StandardLockModeEnum
