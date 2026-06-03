<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Helper\HExpr;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Implement FromInterface for single-table FROM constraints (used by DELETE, UPDATE).
 * @scope Takes only the first table from the array, ignoring the rest.
 * @input Table references (only first element used).
 * @output Normalized single-table FROM clause state.
 * @invariants
 * - addFrom() delegates to from(), enforcing single-table constraint
 * - Only first table in array is applied
 * @modulemap
 * TRAIT SingleFromTrait => FromInterface implementation (single-table)
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: FROM, single-table, trait, SQL, DELETE, UPDATE, constraint
// STRUCTURE: ▶ from(array) → array_slice(0,1) → normalize | addFrom → delegates to from → ∑ [SingleFromTrait methods]

// region TRAIT_SingleFromTrait [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * This trait provides functionality of FromInterface
 * for queries which allow using only single table.
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\FromInterface
 * @purpose Implement FromInterface for queries restricted to a single FROM table.
 */
trait SingleFromTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $from = [];

    // region METHOD_from [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Set a single FROM table, taking only the first element of the array.
     * @io array $tables -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function from(array $tables): static
    {
        if ($tables) {
            $this->from = array_map(
                HExpr::normalizeTable(...),
                array_slice($tables, 0, 1),
            );
        } else {
            $this->from = [];
        }

        return $this;
    }
    // endregion METHOD_from

    // region METHOD_addFrom [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Delegates to from() - enforces single-table constraint.
     * @io array $tables -> static
     * @complexity 1
     *
     * @inheritDoc
     */
    public function addFrom(array $tables): static
    {
        return $this->from($tables);
    }
    // endregion METHOD_addFrom
}
// endregion TRAIT_SingleFromTrait
