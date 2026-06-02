<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Implement WithInterface for CTE (Common Table Expression) support via reusable trait.
 * @scope Manages WITH and WITH RECURSIVE definitions.
 * @input Named WithQuery definitions and recursive flag.
 * @output Normalized CTE clause state via WithInterface contract.
 * @modulemap
 * TRAIT WithTrait => WithInterface implementation (CTE)
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: WITH, CTE, trait, SQL, common table expression, recursive

// region TRAIT_WithTrait [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * This trait provides functionality of WithInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\WithInterface
 * @purpose Implement WithInterface for queries requiring CTE support.
 */
trait WithTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $with = [];
    protected(set) bool $withRecursive = false;

    // region METHOD_with [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Set CTE definitions, replacing existing ones, with optional RECURSIVE flag.
     * @io array $with, bool $recursive -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function with(array $with, bool $recursive = false): static
    {
        $this->withRecursive = $recursive;
        $this->with = $with;

        return $this;
    }
    // endregion METHOD_with

    // region METHOD_addWith [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Merge additional CTE definitions into existing ones.
     * @io array $with, bool $recursive -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function addWith(array $with, bool $recursive = false): static
    {
        $this->withRecursive = $recursive;
        $this->with = array_merge($this->with, $with);

        return $this;
    }
    // endregion METHOD_addWith
}
// endregion TRAIT_WithTrait
