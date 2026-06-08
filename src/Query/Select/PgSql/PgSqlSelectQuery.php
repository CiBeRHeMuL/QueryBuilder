<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Select\PgSql;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Select; CONCEPT(8): PgSqlSelectQuery; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL-specific SELECT query with DISTINCT ON and lock mode support.
 * @scope PostgreSQL SELECT query building with DISTINCT ON extension.
 * @input Columns, tables, conditions, distinct on columns, lock modes
 * @output PgSqlSelectQuery instance with PostgreSQL-specific SELECT capabilities
 * @invariants
 * - DISTINCT is automatically true when distinctOn is non-empty
 * - Setting distinct to false clears distinctOn
 * @modulemap
 * PgSqlSelectQuery => PostgreSQL SELECT query with DISTINCT ON and lock modes
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, SELECT, DISTINCT ON, lock, dialect
// STRUCTURE: ▶ SelectQuery + distinctOn() + addDistinctOn() + addLock() → ∑ [PgSqlSelectQuery]

// region CLASS_PgSqlSelectQuery [DOMAIN(8): Select; CONCEPT(8): PgSqlSelectQuery; TECH(8): Dialect]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @phpstan-template TExpression of TValue|array<TExpression>
 * @purpose PostgreSQL SELECT query extending SelectQuery with DISTINCT ON clause and FOR UPDATE/FOR SHARE lock modes.
 */
class PgSqlSelectQuery extends SelectQuery
{
    /**
     * @var TExpression[] $distinctOn
     */
    protected(set) array $distinctOn = [];
    protected(set) bool $distinct = false {
        get => $this->distinct || !empty($this->distinctOn);
        set {
            $this->distinct = $value;
            if ($this->distinct === false) {
                $this->distinctOn = [];
            }
        }
    }
    /**
     * @var LockModeInterface[] $lockModes
     */
    protected(set) array $lockModes = [];
    protected(set) ?LockModeInterface $lockMode = null {
        set {
            if ($value !== null) {
                $this->lockModes = [$value];
            } else {
                $this->lockModes = [];
            }
        }
        get => array_first($this->lockModes);
    }

    // region METHOD_distinctOn [DOMAIN(8): Select; TECH(8): Distinct]
    /**
     * @param TExpression[] $columns
     *
     * @return PgSqlSelectQuery
     * @purpose Set DISTINCT ON columns for PostgreSQL SELECT.
     */
    public function distinctOn(array $columns): static
    {
        $this->distinctOn = $columns;

        return $this;
    }
    // endregion METHOD_distinctOn

    // region METHOD_addDistinctOn [DOMAIN(8): Select; TECH(8): Distinct]
    /**
     * @param TExpression[] $columns
     *
     * @return PgSqlSelectQuery
     * @purpose Add DISTINCT ON columns for PostgreSQL SELECT.
     */
    public function addDistinctOn(array $columns): static
    {
        $this->distinctOn = array_merge($this->distinctOn, $columns);

        return $this;
    }
    // endregion METHOD_addDistinctOn

    // region METHOD_addLock [DOMAIN(8): Select; TECH(8): Lock]
    /**
     * @purpose Add a lock mode (FOR UPDATE / FOR SHARE) to the SELECT query.
     */
    public function addLock(LockModeInterface $lockMode): static
    {
        $this->lockModes[] = $lockMode;

        return $this;
    }
    // endregion METHOD_addLock
}
// endregion CLASS_PgSqlSelectQuery
