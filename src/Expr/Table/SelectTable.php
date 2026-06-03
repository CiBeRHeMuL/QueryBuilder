<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Table;

// region MODULE_CONTRACT [DOMAIN(8): Table; CONCEPT(7): TableReference; TECH(5): ValueObject]
/**
 * @moduleContract
 * @purpose Represents a table reference used in SELECT, FROM, and JOIN clauses.
 * @scope Simple value object holding a table name.
 * @input Table name string.
 * @output Table reference data for SQL rendering.
 * @modulemap
 * SelectTable => Table reference value object
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: table, FROM, table reference, SQL
// STRUCTURE: ▶ ┌name┐ → ∑ [SelectTable]

// region CLASS_SelectTable [DOMAIN(8): Table; CONCEPT(7): TableReference; TECH(5): ValueObject]
/**
 * @purpose Represents a table reference for SELECT/FROM/JOIN clauses.
 */
class SelectTable
{
    public function __construct(
        protected(set) string $name,
    ) {}
}
// endregion CLASS_SelectTable
