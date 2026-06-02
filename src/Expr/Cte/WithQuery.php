<?php

namespace AndrewGos\QueryBuilder\Expr\Cte;

use AndrewGos\QueryBuilder\Enum\Cte\SearchTypeEnum;
use AndrewGos\QueryBuilder\Expr\Literal;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;

// region MODULE_CONTRACT [DOMAIN(7): CTE; CONCEPT(9): WithClause; TECH(6): FluentBuilder]
/**
 * @moduleContract
 * @purpose Provides a fluent builder for WITH (Common Table Expression) clauses, allowing search and cycle configuration.
 * @scope Building CTE definitions with optional SEARCH and CYCLE clauses.
 * @input Query, optional Search, optional Cycle.
 * @output Configured WithQuery instance for CTE rendering.
 * @modulemap
 * WithQuery => Fluent CTE builder with search/cycle support
 * @invariants
 * - Search and Cycle can be set independently
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: CTE, with clause, recursive query, search, cycle, PostgreSQL, Oracle, SQL Server

// region CLASS_WithQuery [DOMAIN(7): CTE; CONCEPT(9): WithClause; TECH(6): FluentBuilder]
/**
 * @purpose Provides a fluent builder for WITH (Common Table Expression) clauses.
 */
class WithQuery
{
    // region METHOD___construct [DOMAIN(7): CTE; CONCEPT(5): Ctor; TECH(5): DI]
    /**
     * @purpose Initializes the WITH query with an optional SEARCH and CYCLE clause.
     * @io MaybeReturnableQueryInterface, ?Search, ?Cycle -> void
     * @complexity 1
     */
    public function __construct(
        protected(set) MaybeReturnableQueryInterface $query,
        protected(set) ?Search $search = null,
        protected(set) ?Cycle $cycle = null,
    ) {}
    // endregion METHOD___construct

    // region METHOD_search [DOMAIN(7): CTE; CONCEPT(8): SearchClause; TECH(5): FluentAPI]
    /**
     * @purpose Configures a SEARCH clause on this CTE for BFS/DFS traversal ordering.
     * @param SearchTypeEnum $type
     * @param string[] $columns
     * @param string $searchSeqColumnName
     *
     * @return WithQuery
     * @io SearchTypeEnum, array, string -> static
     * @complexity 2
     */
    public function search(
        SearchTypeEnum $type,
        array $columns,
        string $searchSeqColumnName,
    ): static {
        $this->search = new Search($type, $columns, $searchSeqColumnName);

        return $this;
    }
    // endregion METHOD_search

    // region METHOD_cycle [DOMAIN(7): CTE; CONCEPT(8): CycleClause; TECH(5): FluentAPI]
    /**
     * @purpose Configures a CYCLE clause on this CTE for cycle detection.
     * @param string[] $columns
     * @param string $cycleMarkColumnName
     * @param string $cyclePathColumnName
     * @param bool|int|float|string|Literal|null $cycleMarkValue
     * @param bool|int|float|string|Literal|null $cycleMarkDefault
     *
     * @return WithQuery
     * @io array, string, string, Literal|scalar|null, Literal|scalar|null -> static
     * @complexity 3
     */
    public function cycle(
        array $columns,
        string $cycleMarkColumnName,
        string $cyclePathColumnName,
        bool|int|float|string|Literal|null $cycleMarkValue = true,
        bool|int|float|string|Literal|null $cycleMarkDefault = false,
    ): static {
        $cycleMarkValue = $cycleMarkValue instanceof Literal ? $cycleMarkValue : new Literal($cycleMarkValue);
        $cycleMarkDefault = $cycleMarkDefault instanceof Literal ? $cycleMarkDefault : new Literal($cycleMarkDefault);

        $this->cycle = new Cycle(
            $columns,
            $cycleMarkColumnName,
            $cyclePathColumnName,
            $cycleMarkValue,
            $cycleMarkDefault,
        );

        return $this;
    }
    // endregion METHOD_cycle
}
// endregion CLASS_WithQuery
