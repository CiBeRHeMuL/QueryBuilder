<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Group;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(7): GROUP BY; CONCEPT(8): Rollup; TECH(6): SQLStandard]
/**
 * @moduleContract
 * @purpose Represents a ROLLUP grouping expression for GROUP BY clauses, generating hierarchical subtotals.
 * @scope Simple wrapper: passes 'ROLLUP' prefix to AbstractGroupingSets.
 * @input Column expressions.
 * @output ROLLUP SQL fragment.
 * @modulemap
 * Rollup => ROLLUP grouping expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: GROUP BY, ROLLUP, aggregate, SQL standard, OLAP
// STRUCTURE: ▶ ┌columns┐ → parent::__construct('ROLLUP', columns) → ∑ [Rollup]

// region CLASS_Rollup [DOMAIN(7): GROUP BY; CONCEPT(8): Rollup; TECH(6): SQLStandard]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TGroupExpression of ExprInterface|array<TGroupExpression>
 * @purpose Represents a ROLLUP grouping expression for GROUP BY clauses.
 */
class Rollup extends AbstractGroupingSets
{
    /**
     * @param TGroupExpression[] $columns
     */
    public function __construct(
        array $columns,
    ) {
        parent::__construct('ROLLUP', $columns);
    }
}
// endregion CLASS_Rollup
