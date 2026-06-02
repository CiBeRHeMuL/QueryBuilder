<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Helper\HExpr;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Implement FromInterface for multi-table FROM support via reusable trait.
 * @scope Applies HExpr::normalizeTable to all table inputs.
 * @input Table references (strings, expressions, subqueries).
 * @output Normalized FROM clause state via FromInterface contract.
 * @invariants
 * - from() replaces, addFrom() merges
 * @modulemap
 * TRAIT FromTrait => FromInterface implementation (multi-table)
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: FROM, trait, multi-table, SQL, tables, HExpr, normalize

/**
 * This trait provides functionality of FromInterface
 * for queries which allow using not only single table.
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\FromInterface
 */
// region TRAIT_FromTrait [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * @purpose Implement FromInterface for queries allowing multiple FROM tables.
 */
trait FromTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $from = [];

    // region METHOD_from [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Set FROM tables by normalizing each entry via HExpr::normalizeTable.
     * @io array $tables -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function from(array $tables): static
    {
        $this->from = array_map(HExpr::normalizeTable(...), $tables);

        return $this;
    }
    // endregion METHOD_from

    // region METHOD_addFrom [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Append additional normalized tables to the existing FROM clause.
     * @io array $tables -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function addFrom(array $tables): static
    {
        $this->from = array_merge(
            $this->from,
            array_map(HExpr::normalizeTable(...), $tables),
        );
        return $this;
    }
    // endregion METHOD_addFrom
}
// endregion TRAIT_FromTrait
