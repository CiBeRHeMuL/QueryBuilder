<?php

namespace AndrewGos\QueryBuilder\Query\Interface\PgSql;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Interface; CONCEPT(8): ReturningInterface; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose Defines the contract for PostgreSQL RETURNING clause support in INSERT/UPDATE/DELETE queries.
 * @scope PostgreSQL RETURNING clause for DML statements.
 * @input array $columns, ?string $oldAlias, ?string $newAlias
 * @output Interface for RETURNING clause
 * @invariants
 * - $returningColumns null means no RETURNING clause
 * - Supports OLD AS / NEW AS aliases for trigger-based scenarios
 * @modulemap
 * ReturningInterface => PostgreSQL RETURNING clause interface
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, RETURNING, OLD, NEW, interface, dialect

// region INTERFACE_ReturningInterface [DOMAIN(8): Interface; CONCEPT(8): ReturningInterface; TECH(8): Dialect]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|null
 * @template TExpression of TValue|array<TExpression>
 *
 * @template TSelectExpression of TExpression
 *
 * @purpose Contract for queries supporting PostgreSQL RETURNING clause with column selection and OLD/NEW alias support.
 */
interface ReturningInterface extends MaybeReturnableQueryInterface
{
    public ?string $returningOldAlias {
        get;
    }
    public ?string $returningNewAlias {
        get;
    }
    /**
     * @var array<int|string, TSelectExpression>|null
     */
    public ?array $returningColumns {
        get;
    }

    /**
     * @param array<int|string, TSelectExpression> $columns
     * @param string|null $oldAlias
     * @param string|null $newAlias
     *
     * @return static
     */
    public function returning(array $columns, ?string $oldAlias = null, ?string $newAlias = null): static;

    /**
     * @param array<int|string, TSelectExpression> $columns
     * @param string|null $oldAlias
     * @param string|null $newAlias
     *
     * @return static
     */
    public function addReturning(array $columns, ?string $oldAlias = null, ?string $newAlias = null): static;
}
// endregion INTERFACE_ReturningInterface
