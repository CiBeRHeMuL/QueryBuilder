<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Update;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Query\Interface\WhereInterface;
use AndrewGos\QueryBuilder\Query\Interface\WithInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Update; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for UPDATE SQL queries with WITH, WHERE, and SET support. No FROM — single-table UPDATE.
 * @scope Interface extending clause interfaces for UPDATE operations.
 * @input Table name, SET values, and conditions via parent interfaces.
 * @output Contract for UPDATE query DTO.
 * @modulemap
 * INTERFACE UpdateQueryInterface => UPDATE query contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: UPDATE, SQL, interface, query contract, update, WithInterface, WhereInterface, SET
// STRUCTURE: ▶ WithInterface + WhereInterface + table() + set() → ∑ [UpdateQueryInterface contract]

// region INTERFACE_UpdateQueryInterface [DOMAIN(8): Query; CONCEPT(9): Update; TECH(8): SQL]
/**
 * @template TSetValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null
 * @template TSet of array<int, SetClause>|array<string, TSetValue>
 *
 * @purpose Contract composing WITH and WHERE interfaces for UPDATE queries, plus table() and set() methods.
 */
interface UpdateQueryInterface extends WithInterface, WhereInterface
{
    public string $table {
        get;
    }

    /**
     * @var SetClause[]
     */
    public array $set {
        get;
    }

    /**
     * @param string $table target table name for the UPDATE (single table, ANSI SQL)
     *
     * @return static
     */
    public function table(string $table): static;

    /**
     * @param TSet $set short syntax: array<string, TSetValue> (column => value), or pre-built array<int, SetClause>
     *
     * @return static
     */
    public function set(array $set): static;
}
// endregion INTERFACE_UpdateQueryInterface
