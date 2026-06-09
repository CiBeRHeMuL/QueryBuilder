<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Update;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Trait\WhereTrait;
use AndrewGos\QueryBuilder\Query\Trait\WithTrait;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Update; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Build UPDATE SQL queries with WITH, WHERE, and SET clause support. Single-table UPDATE (ANSI SQL).
 * @scope Implementation of UpdateQueryInterface using reusable traits.
 * @input Table name via table(), SET values via set(), optional WHERE conditions.
 * @output Immutable UPDATE query DTO.
 * @invariants
 * - $table must be set before building (validated in grammar).
 * - $set values are normalized to SetClause[].
 * @modulemap
 * CLASS UpdateQuery => UPDATE query implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: UPDATE, SQL, query, update rows, WithTrait, WhereTrait, SET
// STRUCTURE: ▶ WithTrait + WhereTrait + table(name) + set(array→SetClause[]) → ∑ [UpdateQuery]

// region CLASS_UpdateQuery [DOMAIN(8): Query; CONCEPT(9): Update; TECH(8): SQL]
/**
 * @template TSetValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null
 * @template TSet of array<int, SetClause>|array<string, TSetValue>
 *
 * @purpose Concrete UPDATE query object composing WithTrait, WhereTrait, and SET support.
 */
class UpdateQuery implements UpdateQueryInterface
{
    use WithTrait;
    use WhereTrait;

    protected(set) string $table = '';
    /** @var SetClause[] */
    protected(set) array $set = [];

    // region METHOD_table [DOMAIN(8): Query; CONCEPT(9): Update; TECH(8): SQL]
    /**
     * @purpose Set the target table for the UPDATE statement.
     * @complexity 1
     *
     * @param string $table target table name (single table, ANSI SQL)
     *
     * @return static
     */
    public function table(string $table): static
    {
        $this->table = $table;

        return $this;
    }
    // endregion METHOD_table

    // region METHOD_set [DOMAIN(8): Query; CONCEPT(9): Update; TECH(8): SQL]
    /**
     * @purpose Set the SET columns and values, normalizing short syntax (string key => value) into SetClause objects.
     * @complexity 3
     *
     * @param TSet $set short syntax: array<string, TSetValue> (column => value), or pre-built array<int, SetClause>
     *
     * @return static
     */
    public function set(array $set): static
    {
        $normalized = [];
        foreach ($set as $key => $value) {
            if ($value instanceof SetClause) {
                $normalized[] = $value;
            } else {
                $normalized[] = new SetClause((string) $key, $value);
            }
        }
        $this->set = $normalized;

        return $this;
    }
    // endregion METHOD_set
}
// endregion CLASS_UpdateQuery
