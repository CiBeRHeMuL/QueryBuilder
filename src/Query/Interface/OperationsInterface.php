<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Enum\SetOperationEnum;
use AndrewGos\QueryBuilder\Expr\SetOperation\SetOperation;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): SetOperations; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for SQL set operations: UNION, INTERSECT, EXCEPT (both ALL and DISTINCT).
 * @scope Methods to combine multiple SELECT queries via set operations.
 * @input Operation type and one or more SelectQueryInterface queries.
 * @output Contract for set operation chaining.
 * @modulemap
 * INTERFACE OperationsInterface => UNION/INTERSECT/EXCEPT clause contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: UNION, INTERSECT, EXCEPT, set operations, SQL, combine queries

/**
 * This interface provides methods for working with UNION, INTERSECT, EXCEPT clauses
 */
// region INTERFACE_OperationsInterface [DOMAIN(8): Query; CONCEPT(9): SetOperations; TECH(8): SQL]
/**
 * @purpose Define methods for working with UNION, INTERSECT, EXCEPT clauses.
 */
interface OperationsInterface
{
    /**
     * @property SetOperation[]
     */
    public array $operations {
        get;
    }

    // region METHOD_operateWith [DOMAIN(8): Query; CONCEPT(9): SetOperations; TECH(8): SQL]
    /**
     * @purpose Apply a set operation (UNION, INTERSECT, EXCEPT) with one or more queries.
     * @io SetOperationEnum $operation, SelectQueryInterface ...$queries -> static
     * @complexity 3
     *
     * @param SetOperationEnum $operation
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function operateWith(SetOperationEnum $operation, SelectQueryInterface ...$queries): static;
    // endregion METHOD_operateWith

    // region METHOD_unionAll [DOMAIN(8): Query; CONCEPT(9): SetOperations; TECH(8): SQL]
    /**
     * @purpose Apply UNION ALL with one or more queries.
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function unionAll(SelectQueryInterface ...$queries): static;
    // endregion METHOD_unionAll

    // region METHOD_intersectAll [DOMAIN(8): Query; CONCEPT(9): SetOperations; TECH(8): SQL]
    /**
     * @purpose Apply INTERSECT ALL with one or more queries.
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function intersectAll(SelectQueryInterface ...$queries): static;
    // endregion METHOD_intersectAll

    // region METHOD_exceptAll [DOMAIN(8): Query; CONCEPT(9): SetOperations; TECH(8): SQL]
    /**
     * @purpose Apply EXCEPT ALL with one or more queries.
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function exceptAll(SelectQueryInterface ...$queries): static;
    // endregion METHOD_exceptAll

    // region METHOD_unionDistinct [DOMAIN(8): Query; CONCEPT(9): SetOperations; TECH(8): SQL]
    /**
     * @purpose Apply UNION DISTINCT with one or more queries.
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function unionDistinct(SelectQueryInterface ...$queries): static;
    // endregion METHOD_unionDistinct

    // region METHOD_intersectDistinct [DOMAIN(8): Query; CONCEPT(9): SetOperations; TECH(8): SQL]
    /**
     * @purpose Apply INTERSECT DISTINCT with one or more queries.
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function intersectDistinct(SelectQueryInterface ...$queries): static;
    // endregion METHOD_intersectDistinct

    // region METHOD_exceptDistinct [DOMAIN(8): Query; CONCEPT(9): SetOperations; TECH(8): SQL]
    /**
     * @purpose Apply EXCEPT DISTINCT with one or more queries.
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function exceptDistinct(SelectQueryInterface ...$queries): static;
    // endregion METHOD_exceptDistinct
}
// endregion INTERFACE_OperationsInterface
