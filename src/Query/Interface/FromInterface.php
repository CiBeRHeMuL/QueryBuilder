<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): From; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for SQL FROM clause with multi-table support.
 * @scope Methods to set and append table sources.
 * @input Array of table references (string, ExprInterface, subquery, VALUES).
 * @output Contract for FROM clause on any query type.
 * @modulemap
 * INTERFACE FromInterface => FROM clause contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: FROM, SQL, tables, subquery, JOIN source, clause

/**
 * This interface provides methods for working with FROM clause
 *
 * @template TTable of string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable
 * @template TNormalizedTable of ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable
 */
// region INTERFACE_FromInterface [DOMAIN(8): Query; CONCEPT(9): From; TECH(8): SQL]
/**
 * @purpose Define methods for working with FROM clause.
 */
interface FromInterface
{
    /**
     * @var array<int|string, TNormalizedTable>
     */
    public array $from {
        get;
    }

    // region METHOD_from [DOMAIN(8): Query; CONCEPT(9): From; TECH(8): SQL]
    /**
     * @purpose Set the FROM tables, replacing any existing ones.
     * @io array<int|string, TTable> -> static
     * @complexity 2
     *
     * @param array<int|string, TTable> $tables
     *
     * @return static
     */
    public function from(array $tables): static;
    // endregion METHOD_from

    // region METHOD_addFrom [DOMAIN(8): Query; CONCEPT(9): From; TECH(8): SQL]
    /**
     * @purpose Append additional tables to the existing FROM clause.
     * @io array<int|string, TTable> -> static
     * @complexity 2
     *
     * @param array<int|string, TTable> $tables
     *
     * @return static
     */
    public function addFrom(array $tables): static;
    // endregion METHOD_addFrom
}
// endregion INTERFACE_FromInterface
