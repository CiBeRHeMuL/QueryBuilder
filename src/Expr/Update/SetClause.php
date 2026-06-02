<?php

namespace AndrewGos\QueryBuilder\Expr\Update;

use AndrewGos\QueryBuilder\Expr\ExprInterface;

// region MODULE_CONTRACT [DOMAIN(8): UPDATE; CONCEPT(7): SetClause; TECH(5): ValueObject]
/**
 * @moduleContract
 * @purpose Represents a single SET clause in an UPDATE statement (column = value).
 * @scope Value object holding target column and value expression.
 * @input Target column name and value.
 * @output SET clause data for UPDATE rendering.
 * @modulemap
 * SetClause => SET clause value object
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: UPDATE, SET, column assignment, SQL

// region CLASS_SetClause [DOMAIN(8): UPDATE; CONCEPT(7): SetClause; TECH(5): ValueObject]
/**
 * @purpose Represents a single SET clause in an UPDATE statement.
 */
readonly class SetClause
{
    public function __construct(
        protected(set) string $target,
        protected(set) bool|int|float|string|ExprInterface $value,
    ) {}
}
// endregion CLASS_SetClause
