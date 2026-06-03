<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Group;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Expr\AbstractExpr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(7): GROUP BY; CONCEPT(8): GroupingSets; TECH(6): SQLStandard]
/**
 * @moduleContract
 * @purpose Provides the base class for GROUP BY grouping set expressions (CUBE, ROLLUP, GROUPING SETS).
 * @scope Template method pattern: prefix + column list generation for SQL grouping expressions.
 * @input SQL prefix string and array of groupable expressions.
 * @output Rendered SQL fragment with params for CUBE/ROLLUP/GROUPING SETS.
 * @modulemap
 * AbstractGroupingSets => Abstract base for grouping set expressions
 * @invariants
 * - Columns are validated via HExpr::testGroupByExpr
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: GROUP BY, CUBE, ROLLUP, GROUPING SETS, aggregate, SQL standard, OLAP
// STRUCTURE: ▶ Init ┌prefix + columns[]┐ → ○ Loop columns: 〈ValueBuilder::build(column)〉 → ⊕ parts[].expression + ⊕ params → ∑ return ┌prefix┐ + '( ' + implode(',', parts) + ' )' → ⟅string, array⟆

// region CLASS_AbstractGroupingSets [DOMAIN(7): GROUP BY; CONCEPT(8): GroupingSets; TECH(6): SQLStandard]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TGroupExpression of ExprInterface|array<TGroupExpression>
 * @purpose Abstract base for CUBE, ROLLUP, and GROUPING SETS expressions.
 */
abstract class AbstractGroupingSets extends AbstractExpr
{
    /**
     * @param TGroupExpression[] $columns
     */
    public function __construct(
        private string $prefix,
        private array $columns,
    ) {
        HExpr::testGroupByExpr($this->columns);
    }

    // region METHOD_doBuild [DOMAIN(7): GROUP BY; CONCEPT(8): SQLRender; TECH(6): ValueBuilder]
    /**
     * @purpose Builds the SQL fragment for the grouping set expression.
     * @io GrammarInterface -> array{string, array}
     * @complexity 4
     */
    protected function doBuild(GrammarInterface $grammar): array
    {
        $parts = [];
        $params = [];
        $vb = new ValueBuilder();
        foreach ($this->columns as $column) {
            $expr = $vb->build($column, $grammar, true);
            $parts[] = $expr->getExpression($grammar);
            $params = HExpr::mergeParams($params, $expr->getParams());
        }

        return [$this->prefix . ' (' . implode(', ', $parts) . ')', $params];
    }
    // endregion METHOD_doBuild
}
// endregion CLASS_AbstractGroupingSets
