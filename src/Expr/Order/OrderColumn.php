<?php

namespace AndrewGos\QueryBuilder\Expr\Order;

use AndrewGos\QueryBuilder\Expr\ExprInterface;

// region MODULE_CONTRACT [DOMAIN(8): ORDER BY; CONCEPT(7): SortColumn; TECH(5): ValueObject]
/**
 * @moduleContract
 * @purpose Value object representing a single ORDER BY column with its sort direction.
 * @scope Holds expression and sort order string (ASC, DESC, or domain-specific).
 * @input Expression and sort order string.
 * @output Order column data for ORDER BY rendering.
 * @modulemap
 * OrderColumn => Sort column value object
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: ORDER BY, sort, ASC, DESC, column ordering, SQL

// region CLASS_OrderColumn [DOMAIN(8): ORDER BY; CONCEPT(7): SortColumn; TECH(5): ValueObject]
/**
 * @purpose Value object representing a single ORDER BY column with sort direction.
 */
final readonly class OrderColumn
{
    /**
     * @param ExprInterface|int|string $expr
     * @param string $order sort order. Can be simple ASC or DESC or you can use domain specific order like USING for Postgres and NULLS FIRST/LAST
     */
    public function __construct(
        private(set) ExprInterface|int|string $expr,
        private(set) string $order = 'ASC',
    ) {}
}
// endregion CLASS_OrderColumn
