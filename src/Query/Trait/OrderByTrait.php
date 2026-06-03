<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Helper\HExpr;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Implement OrderByInterface for ORDER BY clause support via reusable trait.
 * @scope Normalizes order-by columns via HExpr::normalizeOrderBy.
 * @input Column-order pairs or expressions.
 * @output Normalized ORDER BY clause state via OrderByInterface contract.
 * @modulemap
 * TRAIT OrderByTrait => OrderByInterface implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: ORDER BY, trait, SQL, sorting, ordering, HExpr, normalize
// STRUCTURE: ▶ orderBy(array) → HExpr::normalizeOrderBy | addOrderBy(array) → array_merge + normalize → ∑ [OrderByTrait methods]

// region TRAIT_OrderByTrait [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * This trait provides functionality of OrderByInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\OrderByInterface
 * @purpose Implement OrderByInterface for queries requiring result sorting.
 */
trait OrderByTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $orderBy = [];

    // region METHOD_orderBy [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Set ORDER BY columns via HExpr::normalizeOrderBy, replacing existing.
     * @io array $columns -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function orderBy(array $columns): static
    {
        $this->orderBy = HExpr::normalizeOrderBy($columns);

        return $this;
    }
    // endregion METHOD_orderBy

    // region METHOD_addOrderBy [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Append additional ORDER BY columns via HExpr::normalizeOrderBy.
     * @io array $columns -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function addOrderBy(array $columns): static
    {
        $this->orderBy = array_merge(
            $this->orderBy,
            HExpr::normalizeOrderBy($columns),
        );

        return $this;
    }
    // endregion METHOD_addOrderBy
}
// endregion TRAIT_OrderByTrait
