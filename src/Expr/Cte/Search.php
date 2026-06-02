<?php

namespace AndrewGos\QueryBuilder\Expr\Cte;

use AndrewGos\QueryBuilder\Enum\Cte\SearchTypeEnum;

// region MODULE_CONTRACT [DOMAIN(7): CTE; CONCEPT(8): RecursiveQuery; TECH(5): ValueObject]
/**
 * @moduleContract
 * @purpose Defines a SEARCH clause for recursive CTEs, controlling traversal order (breadth-first or depth-first).
 * @scope Single value object holding search clause configuration.
 * @input Search type (BFS/DFS), column list, sequence column name.
 * @output Search clause data for CTE rendering.
 * @modulemap
 * Search => Search clause value object for recursive CTEs
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: CTE, recursive query, search, breadth-first, depth-first, PostgreSQL, Oracle

// region CLASS_Search [DOMAIN(7): CTE; CONCEPT(8): RecursiveQuery; TECH(5): ValueObject]
/**
 * @purpose Defines a SEARCH clause for recursive CTEs to control traversal order.
 */
final readonly class Search
{
    /**
     * @param SearchTypeEnum $type
     * @param string[] $columns
     * @param string $searchSeqColumnName
     */
    public function __construct(
        private(set) SearchTypeEnum $type,
        private(set) array $columns,
        private(set) string $searchSeqColumnName,
    ) {}
}
// endregion CLASS_Search
