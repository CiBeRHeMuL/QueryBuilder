<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Order\OrderColumn;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Sorting; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for SQL ORDER BY clause.
 * @scope Methods to set and append column ordering with direction.
 * @input Column-order pairs or expressions.
 * @output Contract for result set sorting.
 * @modulemap
 * INTERFACE OrderByInterface => ORDER BY clause contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: ORDER BY, sorting, SQL, ordering, columns, direction

/**
 * This interface provides methods for working with ORDER BY clause
 *
 * @template TOrderBy of array<string, int|string>|array<int, string|ExprInterface|OrderColumn> column => order, expression or OrderColumn
 */
// region INTERFACE_OrderByInterface [DOMAIN(8): Query; CONCEPT(9): Sorting; TECH(8): SQL]
/**
 * @purpose Define methods for working with ORDER BY clause.
 */
interface OrderByInterface
{
    /**
     * @var OrderColumn[]
     */
    public array $orderBy {
        get;
    }

    // region METHOD_orderBy [DOMAIN(8): Query; CONCEPT(9): Sorting; TECH(8): SQL]
    /**
     * @purpose Set the ORDER BY columns, replacing any existing ones.
     * @io TOrderBy $columns -> static
     * @complexity 2
     *
     * @param TOrderBy $columns
     *
     * @return static
     */
    public function orderBy(array $columns): static;
    // endregion METHOD_orderBy

    // region METHOD_addOrderBy [DOMAIN(8): Query; CONCEPT(9): Sorting; TECH(8): SQL]
    /**
     * @purpose Append additional columns to the existing ORDER BY clause.
     * @io TOrderBy $columns -> static
     * @complexity 2
     *
     * @param TOrderBy $columns
     *
     * @return static
     */
    public function addOrderBy(array $columns): static;
    // endregion METHOD_addOrderBy
}
// endregion INTERFACE_OrderByInterface
