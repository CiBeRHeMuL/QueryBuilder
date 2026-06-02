<?php

namespace AndrewGos\QueryBuilder\Expr\SetOperation;

use AndrewGos\QueryBuilder\Enum\SetOperationEnum;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): SetOperation; CONCEPT(8): UnionIntersectExcept; TECH(5): ValueObject]
/**
 * @moduleContract
 * @purpose Represents a single set operation (UNION, INTERSECT, EXCEPT) between two SELECT queries.
 * @scope Value object holding operation type and associated query.
 * @input Operation type and SELECT query.
 * @output Set operation data for compound query rendering.
 * @modulemap
 * SetOperation => Set operation value object
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: UNION, INTERSECT, EXCEPT, set operation, compound query, SQL

// region CLASS_SetOperation [DOMAIN(8): SetOperation; CONCEPT(8): UnionIntersectExcept; TECH(5): ValueObject]
/**
 * @purpose Represents a single set operation (UNION, INTERSECT, EXCEPT) between SELECT queries.
 */
final readonly class SetOperation
{
    public function __construct(
        private(set) SetOperationEnum $operation,
        private(set) SelectQueryInterface $query,
    ) {}
}
// endregion CLASS_SetOperation
