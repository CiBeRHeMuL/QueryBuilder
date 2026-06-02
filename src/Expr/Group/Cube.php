<?php

namespace AndrewGos\QueryBuilder\Expr\Group;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Expr\AbstractExpr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(7): GROUP BY; CONCEPT(8): Cube; TECH(6): SQLStandard]
/**
 * @moduleContract
 * @purpose Represents a CUBE grouping expression for GROUP BY clauses, generating all possible grouping sets.
 * @scope Simple wrapper: passes 'CUBE' prefix to AbstractGroupingSets.
 * @input Column expressions.
 * @output CUBE SQL fragment.
 * @modulemap
 * Cube => CUBE grouping expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: GROUP BY, CUBE, aggregate, SQL standard, OLAP

// region CLASS_Cube [DOMAIN(7): GROUP BY; CONCEPT(8): Cube; TECH(6): SQLStandard]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TGroupExpression of ExprInterface|array<TGroupExpression>
 * @purpose Represents a CUBE grouping expression for GROUP BY clauses.
 */
class Cube extends AbstractGroupingSets
{
    /**
     * @param TGroupExpression[] $columns
     */
    public function __construct(
        array $columns,
    ) {
        parent::__construct('CUBE', $columns);
    }
}
// endregion CLASS_Cube
