<?php

namespace AndrewGos\QueryBuilder\Query\Select;

use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Expr\OrExpr;
use AndrewGos\QueryBuilder\Expr\Window\Window;
use AndrewGos\QueryBuilder\Query\Trait\FromTrait;
use AndrewGos\QueryBuilder\Query\Trait\JoinTrait;
use AndrewGos\QueryBuilder\Query\Trait\LimitTrait;
use AndrewGos\QueryBuilder\Query\Trait\OperationsTrait;
use AndrewGos\QueryBuilder\Query\Trait\OrderByTrait;
use AndrewGos\QueryBuilder\Query\Trait\WhereTrait;
use AndrewGos\QueryBuilder\Query\Trait\WithTrait;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Build complete SELECT SQL queries with all standard clauses (WITH, FROM, JOIN, WHERE, GROUP BY, HAVING, WINDOW, ORDER BY, LIMIT, LOCK, set operations).
 * @scope Full implementation of SelectQueryInterface using 7 reusable traits.
 * @input Columns, tables, conditions, ordering, limits, lock modes, window definitions, set operations.
 * @output Immutable SELECT query DTO (returnable).
 * @invariants
 * - Always returns true from isReturnable()
 * - Uses 7 traits: WithTrait, FromTrait, WhereTrait, JoinTrait, OperationsTrait, OrderByTrait, LimitTrait
 * @modulemap
 * CLASS SelectQuery => SELECT query implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: SELECT, SQL, query, select, columns, FROM, JOIN, WHERE, GROUP BY, HAVING, WINDOW, ORDER BY, LIMIT, LOCK, UNION, set operations

// region CLASS_SelectQuery [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
/**
 * @purpose Concrete SELECT query composing 7 traits for full SQL clause support.
 */
class SelectQuery implements SelectQueryInterface
{
    use WithTrait;
    use FromTrait;
    use WhereTrait;
    use JoinTrait;
    use OperationsTrait;
    use OrderByTrait;
    use LimitTrait;

    /**
     * @inheritDoc
     */
    protected(set) array $selectColumns = [];
    protected(set) bool $distinct = false;
    /**
     * @inheritDoc
     */
    protected(set) array $groupBy = [];
    protected(set) bool $groupDistinct = false;
    /**
     * @inheritDoc
     */
    protected(set) array $having = [];
    /**
     * @inheritDoc
     */
    protected(set) array $windows = [];
    protected(set) ?LockModeInterface $lockMode = null;

    // region METHOD_select [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Set the SELECT columns, replacing any existing ones.
     * @io array $columns -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function select(array $columns): static
    {
        $this->selectColumns = $columns;
        return $this;
    }
    // endregion METHOD_select

    // region METHOD_addSelect [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Append additional columns to the existing SELECT list.
     * @io array $columns -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function addSelect(array $columns): static
    {
        $this->selectColumns = array_merge(
            $this->selectColumns,
            $columns,
        );
        return $this;
    }
    // endregion METHOD_addSelect

    // region METHOD_distinct [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Enable or disable DISTINCT mode on the SELECT.
     * @io bool $distinct -> static
     * @complexity 1
     */
    public function distinct(bool $distinct = true): static
    {
        $this->distinct = $distinct;

        return $this;
    }
    // endregion METHOD_distinct

    // region METHOD_groupBy [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Set GROUP BY columns, optionally with DISTINCT modifier.
     * @io array $columns, bool $distinct -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function groupBy(array $columns, bool $distinct = false): static
    {
        $this->groupDistinct = $distinct;
        $this->groupBy = $columns;

        return $this;
    }
    // endregion METHOD_groupBy

    // region METHOD_addGroupBy [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Append additional columns to the existing GROUP BY clause.
     * @io array $columns, bool $distinct -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function addGroupBy(array $columns, bool $distinct = false): static
    {
        $this->groupDistinct = $distinct;
        $this->groupBy = array_merge($this->groupBy, $columns);

        return $this;
    }
    // endregion METHOD_addGroupBy

    // region METHOD_having [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Set HAVING conditions, replacing any existing ones.
     * @io array|ExprInterface $conditions -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function having(array|ExprInterface $conditions): static
    {
        $this->having = $conditions instanceof ExprInterface ? [$conditions] : $conditions;

        return $this;
    }
    // endregion METHOD_having

    // region METHOD_andHaving [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Append conditions with AND logic to existing HAVING clause.
     * @io array|ExprInterface $conditions -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function andHaving(array|ExprInterface $conditions): static
    {
        $this->having = array_merge(
            $this->having,
            $conditions instanceof ExprInterface ? [$conditions] : $conditions,
        );

        return $this;
    }
    // endregion METHOD_andHaving

    // region METHOD_orHaving [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Append conditions with OR logic, wrapping existing AND group in HAVING.
     * @io array|ExprInterface $conditions -> static
     * @complexity 3
     *
     * @inheritDoc
     */
    public function orHaving(array|ExprInterface $conditions): static
    {
        $this->having = [
            new OrExpr([
                new AndExpr($this->having),
                new AndExpr($conditions instanceof ExprInterface ? [$conditions] : $conditions),
            ]),
        ];

        return $this;
    }
    // endregion METHOD_orHaving

    // region METHOD_window [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Define a named WINDOW for use with window functions.
     * @io string $name, Window $windowDefinition -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function window(string $name, Window $windowDefinition): static
    {
        $this->windows[$name] = $windowDefinition;

        return $this;
    }
    // endregion METHOD_window

    // region METHOD_lock [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Apply a lock mode (FOR UPDATE, FOR SHARE, etc.) to the SELECT.
     * @io LockModeInterface|null $lockMode -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function lock(?LockModeInterface $lockMode): static
    {
        $this->lockMode = $lockMode;

        return $this;
    }
    // endregion METHOD_lock

    // region METHOD_isReturnable [DOMAIN(8): Query; CONCEPT(10): Select; TECH(8): SQL]
    /**
     * @purpose Indicate that SELECT query always returns rows.
     * @io void -> bool
     * @complexity 1
     */
    public function isReturnable(): bool
    {
        return true;
    }
    // endregion METHOD_isReturnable
}
// endregion CLASS_SelectQuery
