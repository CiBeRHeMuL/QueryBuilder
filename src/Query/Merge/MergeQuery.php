<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Merge;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenMatchedClause;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenNotMatchedBySourceClause;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenNotMatchedClause;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Trait\WithTrait;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(10): MergeQuery; TECH(8): ANSI_SQL]
/**
 * @moduleContract
 * @purpose Concrete ANSI SQL:2008 MERGE query implementation with CTE support (via WithTrait). Supports into, using, on, whenMatched, whenNotMatched.
 * @scope ANSI MERGE query DTO.
 * @input Target table, source, join condition, WHEN clauses.
 * @output Immutable MERGE query DTO ready for grammar rendering.
 * @invariants
 * - using source is normalized: string → SelectTable.
 * - whenMatchedClauses and whenNotMatchedClauses are always arrays (possibly empty).
 * @modulemap
 * MergeQuery => ANSI MERGE query implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MergeQuery, MERGE, ANSI, SQL:2008, query, upsert, WithTrait
// STRUCTURE: ▶ WithTrait + into(table, alias) + using(source, alias) + on(conditions) + whenMatched(clauses) + whenNotMatched(clauses) → ∑ [MergeQuery]

// region CLASS_MergeQuery [DOMAIN(8): Merge; CONCEPT(10): MergeQuery; TECH(8): ANSI_SQL]
/**
 * @purpose ANSI SQL:2008 MERGE query with CTE support. Target table via into(), data source via using(), join condition via on(), actions via whenMatched/whenNotMatched.
 */
class MergeQuery implements MergeQueryInterface
{
    use WithTrait;

    protected(set) string $into = '';
    protected(set) ?string $alias = null;
    protected(set) SelectTable|SelectQueryInterface|ValuesQueryInterface|ExprInterface $using;
    protected(set) ?string $usingAlias = null;
    /** @var array<string, mixed> */
    protected(set) array $on = [];
    /** @var MergeWhenMatchedClause[] */
    protected(set) array $whenMatchedClauses = [];
    /** @var MergeWhenNotMatchedClause[] */
    protected(set) array $whenNotMatchedClauses = [];
    /** @var MergeWhenNotMatchedBySourceClause[] */
    protected(set) array $whenNotMatchedBySourceClauses = [];

    // region METHOD_into [DOMAIN(8): Merge; CONCEPT(8): Into; TECH(7): SQL]
    /**
     * @purpose Set the target table for the MERGE operation.
     * @complexity 1
     *
     * @param string      $table target table name
     * @param string|null $alias optional alias for target table
     *
     * @return static
     */
    public function into(string $table, ?string $alias = null): static
    {
        $this->into = $table;
        $this->alias = $alias;

        return $this;
    }
    // endregion METHOD_into

    // region METHOD_using [DOMAIN(8): Merge; CONCEPT(8): Using; TECH(7): SQL]
    /**
     * @purpose Set the data source for the MERGE operation. Strings are normalized to SelectTable.
     * @complexity 2
     *
     * @param string|SelectTable|SelectQueryInterface|ValuesQueryInterface|ExprInterface $source source table name, SelectTable, SELECT/VALUES query, or raw expression
     * @param string|null                                                                $alias  optional alias for source
     *
     * @return static
     */
    public function using(
        string|SelectTable|SelectQueryInterface|ValuesQueryInterface|ExprInterface $source,
        ?string $alias = null,
    ): static {
        $this->using = is_string($source) ? HExpr::normalizeTable($source) : $source;
        $this->usingAlias = $alias;

        return $this;
    }
    // endregion METHOD_using

    // region METHOD_on [DOMAIN(8): Merge; CONCEPT(8): On; TECH(7): SQL]
    /**
     * @purpose Set the join condition for the MERGE operation (ON clause).
     * @complexity 1
     *
     * @param array<string, mixed> $conditions Conditions keyed by target column (e.g., ['t.id' => 's.id']).
     *
     * @return static
     */
    public function on(array $conditions): static
    {
        $this->on = $conditions;

        return $this;
    }
    // endregion METHOD_on

    // region METHOD_whenMatched [DOMAIN(8): Merge; CONCEPT(8): WhenMatched; TECH(7): SQL]
    /**
     * @purpose Add WHEN MATCHED THEN action clauses.
     * @complexity 2
     *
     * @param MergeWhenMatchedClause ...$clauses One or more WHEN MATCHED clauses.
     *
     * @return static
     */
    public function whenMatched(MergeWhenMatchedClause ...$clauses): static
    {
        $this->whenMatchedClauses = array_merge($this->whenMatchedClauses, $clauses);

        return $this;
    }
    // endregion METHOD_whenMatched

    // region METHOD_whenNotMatched [DOMAIN(8): Merge; CONCEPT(8): WhenNotMatched; TECH(7): SQL]
    /**
     * @purpose Add WHEN NOT MATCHED (BY TARGET) THEN action clauses.
     * @complexity 2
     *
     * @param MergeWhenNotMatchedClause ...$clauses One or more WHEN NOT MATCHED clauses.
     *
     * @return static
     */
    public function whenNotMatched(MergeWhenNotMatchedClause ...$clauses): static
    {
        $this->whenNotMatchedClauses = array_merge($this->whenNotMatchedClauses, $clauses);

        return $this;
    }
    // endregion METHOD_whenNotMatched

    // region METHOD_whenNotMatchedBySource [DOMAIN(8): Merge; CONCEPT(8): BySource; TECH(7): SQL]
    /**
     * @purpose Add WHEN NOT MATCHED BY SOURCE THEN action clauses.
     * @complexity 2
     *
     * @param MergeWhenNotMatchedBySourceClause ...$clauses One or more BY SOURCE clauses.
     *
     * @return static
     */
    public function whenNotMatchedBySource(MergeWhenNotMatchedBySourceClause ...$clauses): static
    {
        $this->whenNotMatchedBySourceClauses = array_merge($this->whenNotMatchedBySourceClauses, $clauses);

        return $this;
    }
    // endregion METHOD_whenNotMatchedBySource
}
// endregion CLASS_MergeQuery
