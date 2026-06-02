<?php

namespace AndrewGos\QueryBuilder\Query\Insert;

use AndrewGos\QueryBuilder\Query\Insert\InsertQueryInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Trait\WithTrait;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Insert; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Build INSERT SQL queries with WITH, INTO, and source (SELECT/VALUES) clause support.
 * @scope Implementation of InsertQueryInterface using WithTrait.
 * @input Target table via into(), optional source via source().
 * @output Immutable INSERT query DTO.
 * @invariants
 * - source = null represents "DEFAULT VALUES"
 * @modulemap
 * CLASS InsertQuery => INSERT query implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: INSERT, SQL, query, insert, INTO, DEFAULT VALUES, WithTrait

// region CLASS_InsertQuery [DOMAIN(8): Query; CONCEPT(9): Insert; TECH(8): SQL]
/**
 * @purpose Concrete INSERT query object with CTE, INTO, and source support.
 */
class InsertQuery implements InsertQueryInterface
{
    use WithTrait;

    protected(set) string $into;
    protected(set) ?string $alias = null;
    /**
     * @inheritDoc
     */
    protected(set) array $columnNames = [];
    /**
     * @inheritDoc
     */
    protected(set) SelectQueryInterface|ValuesQueryInterface|null $source = null;

    // region METHOD_into [DOMAIN(8): Query; CONCEPT(9): Insert; TECH(8): SQL]
    /**
     * @purpose Set the target table, column names, and alias for the INSERT.
     * @io string $table, array $columnNames, ?string $alias -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function into(string $table, array $columnNames = [], ?string $alias = null): static
    {
        $this->into = $table;
        $this->columnNames = $columnNames;
        $this->alias = $alias;

        return $this;
    }
    // endregion METHOD_into

    // region METHOD_source [DOMAIN(8): Query; CONCEPT(9): Insert; TECH(8): SQL]
    /**
     * @purpose Set the source query or VALUES for insertion. NULL means DEFAULT VALUES.
     * @io ValuesQueryInterface|SelectQueryInterface|null $source -> static
     * @complexity 2
     */
    public function source(ValuesQueryInterface|SelectQueryInterface|null $source): static
    {
        $this->source = $source;

        return $this;
    }
    // endregion METHOD_source
}
// endregion CLASS_InsertQuery
