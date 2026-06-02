<?php

namespace AndrewGos\QueryBuilder\Query\Values;

use AndrewGos\QueryBuilder\Query\Trait\LimitTrait;
use AndrewGos\QueryBuilder\Query\Trait\OperationsTrait;
use AndrewGos\QueryBuilder\Query\Trait\OrderByTrait;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Values; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Build VALUES SQL queries (inline value lists) with ORDER BY, LIMIT, and set operations support.
 * @scope Implementation of ValuesQueryInterface using OperationsTrait, OrderByTrait, LimitTrait.
 * @input Nested value arrays via values()/addValues().
 * @output Immutable VALUES query DTO (returnable).
 * @invariants
 * - Always returns true from isReturnable()
 * @modulemap
 * CLASS ValuesQuery => VALUES query implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: VALUES, SQL, query, value lists, inline values, ORDER BY, LIMIT, set operations

// region CLASS_ValuesQuery [DOMAIN(8): Query; CONCEPT(9): Values; TECH(8): SQL]
/**
 * @purpose Concrete VALUES query composing OperationsTrait, OrderByTrait, and LimitTrait.
 */
class ValuesQuery implements ValuesQueryInterface
{
    use OperationsTrait;
    use OrderByTrait;
    use LimitTrait;

    protected(set) array $values;

    // region METHOD_values [DOMAIN(8): Query; CONCEPT(9): Values; TECH(8): SQL]
    /**
     * @purpose Set the value rows, replacing any existing ones.
     * @io array $values -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function values(array $values): static
    {
        $this->values = $values;

        return $this;
    }
    // endregion METHOD_values

    // region METHOD_addValues [DOMAIN(8): Query; CONCEPT(9): Values; TECH(8): SQL]
    /**
     * @purpose Append additional value rows to the existing list.
     * @io array $values -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function addValues(array $values): static
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }
    // endregion METHOD_addValues

    // region METHOD_isReturnable [DOMAIN(8): Query; CONCEPT(9): Values; TECH(8): SQL]
    /**
     * @purpose Indicate that VALUES query always returns rows (like SELECT).
     * @io void -> bool
     * @complexity 1
     */
    public function isReturnable(): bool
    {
        return true;
    }
    // endregion METHOD_isReturnable
}
// endregion CLASS_ValuesQuery
