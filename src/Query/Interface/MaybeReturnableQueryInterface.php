<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(7): Returnable; TECH(7): SQL]
/**
 * @moduleContract
 * @purpose Mark a query as potentially returning result rows (SELECT, VALUES, or RETURNING queries).
 * @scope Simple marker interface with one method.
 * @input Query state.
 * @output Boolean indicating whether the query can return values.
 * @modulemap
 * INTERFACE MaybeReturnableQueryInterface => Returnable query marker
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: returnable, query, SQL, marker interface, isReturnable

// region INTERFACE_MaybeReturnableQueryInterface [DOMAIN(8): Query; CONCEPT(7): Returnable; TECH(7): SQL]
/**
 * @purpose Indicate that query can return values after execution (SELECT, VALUES, queries with RETURNING in PostgreSQL).
 */
interface MaybeReturnableQueryInterface
{
    // region METHOD_isReturnable [DOMAIN(8): Query; CONCEPT(7): Returnable; TECH(7): SQL]
    /**
     * @purpose Determine if this query type produces result rows.
     * @io void -> bool
     * @complexity 1
     */
    public function isReturnable(): bool;
    // endregion METHOD_isReturnable
}
// endregion INTERFACE_MaybeReturnableQueryInterface
