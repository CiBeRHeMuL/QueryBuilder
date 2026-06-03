<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Insert;

use AndrewGos\QueryBuilder\Query\Interface\WithInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Insert; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for INSERT SQL queries with INTO and source (SELECT/VALUES) support.
 * @scope Interface for INSERT operations with CTE support (via WithInterface).
 * @input Target table, column names, alias, and optional source query.
 * @output Contract for INSERT query DTO.
 * @modulemap
 * INTERFACE InsertQueryInterface => INSERT query contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: INSERT, SQL, query, insert rows, INTO, source, DEFAULT VALUES
// STRUCTURE: ▶ WithInterface + into() + source() → ∑ [InsertQueryInterface contract]

// region INTERFACE_InsertQueryInterface [DOMAIN(8): Query; CONCEPT(9): Insert; TECH(8): SQL]
/**
 * @purpose Define the contract for INSERT SQL queries.
 */
interface InsertQueryInterface extends WithInterface
{
    public string $into {
        get;
    }
    public ?string $alias {
        get;
    }
    /**
     * @var string[] $columnNames
     */
    public array $columnNames {
        get;
    }
    /**
     * Source for insertion. NULL means "DEFAULT VALUES"
     *
     * @var SelectQueryInterface|ValuesQueryInterface|null $source
     */
    public SelectQueryInterface|ValuesQueryInterface|null $source {
        get;
    }

    // region METHOD_into [DOMAIN(8): Query; CONCEPT(9): Insert; TECH(8): SQL]
    /**
     * @purpose Set the target table, optional columns, and alias for the INSERT.
     * @io string $table, string[] $columnNames, ?string $alias -> static
     * @complexity 2
     *
     * @param string $table
     * @param string[] $columnNames
     * @param string|null $alias
     *
     * @return $this
     */
    public function into(string $table, array $columnNames = [], ?string $alias = null): static;
    // endregion METHOD_into

    // region METHOD_source [DOMAIN(8): Query; CONCEPT(9): Insert; TECH(8): SQL]
    /**
     * @purpose Sets source for insertion. NULL means "DEFAULT VALUES".
     * @io SelectQueryInterface|ValuesQueryInterface|null $source -> static
     * @complexity 2
     *
     * @param SelectQueryInterface|ValuesQueryInterface|null $source
     *
     * @return $this
     */
    public function source(SelectQueryInterface|ValuesQueryInterface|null $source): static;
    // endregion METHOD_source
}
// endregion INTERFACE_InsertQueryInterface
