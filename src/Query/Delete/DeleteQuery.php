<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Delete;

use AndrewGos\QueryBuilder\Query\Trait\SingleFromTrait;
use AndrewGos\QueryBuilder\Query\Trait\WhereTrait;
use AndrewGos\QueryBuilder\Query\Trait\WithTrait;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Delete; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Build DELETE SQL queries with WITH, FROM, and WHERE clause support.
 * @scope Implementation of DeleteQueryInterface using reusable traits.
 * @input Table name via SingleFromTrait, optional WHERE conditions.
 * @output Immutable DELETE query DTO.
 * @invariants
 * - Delegates FROM handling to SingleFromTrait (single-table constraint)
 * @modulemap
 * CLASS DeleteQuery => DELETE query implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: DELETE, SQL, query, delete rows, WithTrait, SingleFromTrait, WhereTrait
// STRUCTURE: ▶ WithTrait + SingleFromTrait + WhereTrait → ∑ [DeleteQuery]

// region CLASS_DeleteQuery [DOMAIN(8): Query; CONCEPT(9): Delete; TECH(8): SQL]
/**
 * @purpose Concrete DELETE query object composing WithTrait, SingleFromTrait, and WhereTrait.
 */
class DeleteQuery implements DeleteQueryInterface
    // endregion CLASS_DeleteQuery
{
    use WithTrait;
    use SingleFromTrait;
    use WhereTrait;
}
