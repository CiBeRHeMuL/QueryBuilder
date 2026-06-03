<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Cte;

use AndrewGos\QueryBuilder\Expr\Literal;

// region MODULE_CONTRACT [DOMAIN(7): CTE; CONCEPT(8): RecursiveQuery; TECH(5): ValueObject]
/**
 * @moduleContract
 * @purpose Defines a CYCLE clause for recursive CTEs, enabling cycle detection in hierarchical queries.
 * @scope Single value object holding cycle detection configuration.
 * @input Column list, mark column name, path column name, mark values.
 * @output Cycle clause data for CTE rendering.
 * @modulemap
 * Cycle => Cycle detection value object for recursive CTEs
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: CTE, cycle detection, recursive query, Oracle, PostgreSQL
// STRUCTURE: ▶ __construct ┌columns, cycleMarkCol, cyclePathCol, markValue, markDefault┐ → ∑ [Cycle value object]

// region CLASS_Cycle [DOMAIN(7): CTE; CONCEPT(8): RecursiveQuery; TECH(5): ValueObject]
/**
 * @purpose Defines a CYCLE clause for recursive CTEs to detect cycles.
 */
final readonly class Cycle
{
    /**
     * @param string[] $columns
     * @param string $cycleMarkColumnName
     * @param string $cyclePathColumnName
     * @param Literal $cycleMarkValue
     * @param Literal $cycleMarkDefault
     */
    public function __construct(
        private(set) array $columns,
        private(set) string $cycleMarkColumnName,
        private(set) string $cyclePathColumnName,
        private(set) Literal $cycleMarkValue = new Literal(true),
        private(set) Literal $cycleMarkDefault = new Literal(false),
    ) {}
}
// endregion CLASS_Cycle
