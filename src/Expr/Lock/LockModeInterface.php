<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Lock;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Lock; CONCEPT(8): LockMode; TECH(5): SQLStandard]
/**
 * @moduleContract
 * @purpose Defines the interface for lock mode implementations (e.g., FOR UPDATE, FOR SHARE).
 * @scope Contract for grammar-specific lock mode SQL generation.
 * @input GrammarInterface.
 * @output SQL string for the lock clause.
 * @modulemap
 * LockModeInterface => Contract for lock mode SQL generation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: LOCK, FOR UPDATE, FOR SHARE, lock mode, SQL, pessimistic locking
// STRUCTURE: ▶ getSql(GrammarInterface) → string [LockModeInterface contract]

// region INTERFACE_LockModeInterface [DOMAIN(8): Lock; CONCEPT(8): LockMode; TECH(5): SQLStandard]
/**
 * @purpose Defines the interface for lock mode implementations.
 */
interface LockModeInterface
{
    /**
     * @purpose Returns the SQL fragment for this lock mode.
     * @io GrammarInterface -> string
     * @complexity 1
     */
    public function getSql(GrammarInterface $grammar): string;
}
// endregion INTERFACE_LockModeInterface
