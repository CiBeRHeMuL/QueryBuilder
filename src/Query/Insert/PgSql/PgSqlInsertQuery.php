<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Insert\PgSql;

use AndrewGos\QueryBuilder\Enum\Insert\PgSql\PgSqlOverrideValueMethodEnum;
use AndrewGos\QueryBuilder\Expr\Conflict\ConflictActionInterface;
use AndrewGos\QueryBuilder\Expr\Conflict\ConflictTargetInterface;
use AndrewGos\QueryBuilder\Query\Insert\InsertQuery;
use AndrewGos\QueryBuilder\Query\Interface\PgSql\ReturningInterface;
use AndrewGos\QueryBuilder\Query\Trait\PgSql\ReturningTrait;

// region MODULE_CONTRACT [DOMAIN(8): Insert; CONCEPT(8): PgSqlInsertQuery; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL-specific INSERT query with OVERRIDING USER/SYSTEM VALUE, ON CONFLICT, and RETURNING clause support.
 * @scope PostgreSQL INSERT query building.
 * @input Table, columns, values, override method, conflict action+target, returning columns
 * @output PgSqlInsertQuery instance with PostgreSQL-specific INSERT capabilities
 * @invariants
 * - Implements ReturningInterface for RETURNING clause
 * - onConflict() requires action (required), target optional
 * @modulemap
 * PgSqlInsertQuery => PostgreSQL INSERT query with OVERRIDING, ON CONFLICT, and RETURNING
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, INSERT, OVERRIDING, ON CONFLICT, RETURNING, dialect
// STRUCTURE: ▶ InsertQuery + ReturningTrait + overrideValue() + onConflict() → ∑ [PgSqlInsertQuery]

// region CLASS_PgSqlInsertQuery [DOMAIN(8): Insert; CONCEPT(8): PgSqlInsertQuery; TECH(8): Dialect]
/**
 * @purpose PostgreSQL INSERT query extending InsertQuery with OVERRIDING USER/SYSTEM VALUE, ON CONFLICT, and RETURNING clause support.
 */
class PgSqlInsertQuery extends InsertQuery implements ReturningInterface
{
    use ReturningTrait;

    protected(set) ?PgSqlOverrideValueMethodEnum $overrideValueMethod = null;

    protected(set) ?ConflictTargetInterface $conflictTarget = null;

    protected(set) ?ConflictActionInterface $conflictAction = null;

    // region METHOD_overrideValue [DOMAIN(8): Insert; CONCEPT(8): OverrideValue; TECH(8): Dialect]
    /**
     * @purpose Set OVERRIDING USER VALUE or OVERRIDING SYSTEM VALUE for PostgreSQL INSERT.
     * @param PgSqlOverrideValueMethodEnum|null $method
     * @return $this
     */
    public function overrideValue(?PgSqlOverrideValueMethodEnum $method): static
    {
        $this->overrideValueMethod = $method;

        return $this;
    }
    // endregion METHOD_overrideValue

    // region METHOD_onConflict [DOMAIN(8): Insert; CONCEPT(8): OnConflict; TECH(8): Dialect]
    /**
     * @purpose Set ON CONFLICT action and optional target for PostgreSQL INSERT.
     * @param ConflictActionInterface $action Required conflict action (DO NOTHING or DO UPDATE SET).
     * @param ConflictTargetInterface|null $target Optional conflict target (columns or ON CONSTRAINT).
     * @return $this
     */
    public function onConflict(ConflictActionInterface $action, ?ConflictTargetInterface $target = null): static
    {
        $this->conflictAction = $action;
        $this->conflictTarget = $target;

        return $this;
    }
    // endregion METHOD_onConflict
}
// endregion CLASS_PgSqlInsertQuery
