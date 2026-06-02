<?php

namespace AndrewGos\QueryBuilder\Query\Trait\PgSql;

// region MODULE_CONTRACT [DOMAIN(8): Trait; CONCEPT(8): ReturningTrait; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose Provides shared implementation of PostgreSQL RETURNING clause functionality for queries.
 * @scope PostgreSQL RETURNING clause implementation trait.
 * @input array $columns, ?string $oldAlias, ?string $newAlias
 * @output ReturningTrait methods for RETURNING clause management
 * @invariants
 * - returningColumns null means no RETURNING clause
 * @modulemap
 * ReturningTrait => PostgreSQL RETURNING clause implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, RETURNING, OLD, NEW, trait, dialect

// region TRAIT_ReturningTrait [DOMAIN(8): Trait; CONCEPT(8): ReturningTrait; TECH(8): Dialect]
/**
 * @purpose Provides shared implementation of ReturningInterface for PostgreSQL RETURNING clause support with OLD/NEW aliases.
 */
trait ReturningTrait
{
    protected(set) ?string $returningOldAlias = null;
    protected(set) ?string $returningNewAlias = null;
    /**
     * @inheritDoc
     */
    protected(set) ?array $returningColumns = null;

    // region METHOD_returning [DOMAIN(8): Trait; TECH(8): Returning]
    /**
     * @inheritDoc
     * @purpose Set RETURNING columns with optional OLD/NEW aliases.
     */
    public function returning(array $columns, ?string $oldAlias = null, ?string $newAlias = null): static
    {
        $this->returningColumns = $columns;
        $this->returningOldAlias = $oldAlias;
        $this->returningNewAlias = $newAlias;

        return $this;
    }
    // endregion METHOD_returning

    // region METHOD_addReturning [DOMAIN(8): Trait; TECH(8): Returning]
    /**
     * @inheritDoc
     * @purpose Add RETURNING columns with optional OLD/NEW aliases.
     */
    public function addReturning(array $columns, ?string $oldAlias = null, ?string $newAlias = null): static
    {
        $this->returningColumns = array_merge($this->returningColumns ?? [], $columns);
        $this->returningOldAlias = $oldAlias;
        $this->returningNewAlias = $newAlias;

        return $this;
    }
    // endregion METHOD_addReturning

    // region METHOD_isReturnable [DOMAIN(8): Returning; TECH(8): Compatibility]
    /**
     * @inheritDoc
     * @purpose Determine if this query type produces result rows (true if RETURNING clause is specified).
     */
    public function isReturnable(): bool
    {
        return $this->returningColumns !== null;
    }
    // endregion METHOD_isReturnable
}
// endregion TRAIT_ReturningTrait
