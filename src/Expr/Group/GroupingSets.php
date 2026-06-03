<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Group;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(7): GROUP BY; CONCEPT(8): GroupingSets; TECH(6): SQLStandard]
/**
 * @moduleContract
 * @purpose Represents a GROUPING SETS expression for GROUP BY clauses, allowing explicit specification of grouping sets.
 * @scope Simple wrapper: passes 'GROUPING SETS' prefix to AbstractGroupingSets.
 * @input Column expressions.
 * @output GROUPING SETS SQL fragment.
 * @modulemap
 * GroupingSets => GROUPING SETS expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: GROUP BY, GROUPING SETS, aggregate, SQL standard, OLAP
// STRUCTURE: ▶ ┌columns┐ → parent::__construct('GROUPING SETS', columns) → ∑ [GroupingSets]

// region CLASS_GroupingSets [DOMAIN(7): GROUP BY; CONCEPT(8): GroupingSets; TECH(6): SQLStandard]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TGroupExpression of ExprInterface|array<TGroupExpression>
 * @purpose Represents a GROUPING SETS expression for GROUP BY clauses.
 */
class GroupingSets extends AbstractGroupingSets
{
    /**
     * @param TGroupExpression[] $columns
     */
    public function __construct(
        array $columns,
    ) {
        parent::__construct('GROUPING SETS', $columns);
    }
}
// endregion CLASS_GroupingSets
