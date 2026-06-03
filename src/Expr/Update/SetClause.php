<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Update;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): UPDATE; CONCEPT(7): SetClause; TECH(5): ValueObject]
/**
 * @moduleContract
 * @purpose Represents a single SET clause element in an UPDATE statement. Supports single-column (string target) and multi-column (string[] target) assignments.
 * @scope Value object holding target column(s) and value expression(s).
 * @input Target column name(s) and value expression.
 * @output SET clause data for UPDATE rendering.
 * @modulemap
 * SetClause => SET clause value object
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: UPDATE, SET, column assignment, SQL, multi-column

// region CLASS_SetClause [DOMAIN(8): UPDATE; CONCEPT(7): SetClause; TECH(5): ValueObject]
/**
 * @purpose Represents a single SET clause element in an UPDATE statement. Target may be a single column (string) or multiple columns (string[]).
 */
readonly class SetClause
{
    /**
     * @param string|string[] $target Column name or array of column names for multi-column assignment.
     * @param bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null $value Value to assign.
     */
    public function __construct(
        protected(set) string|array $target,
        protected(set) bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null $value,
    ) {}

    // region METHOD_getSql [DOMAIN(8): UPDATE; CONCEPT(7): SetClause; TECH(7): Rendering]
    /**
     * @purpose Render this SET clause element to an SQL expression with params. Handles single-column, multi-column, subquery, and array values.
     * @param GrammarInterface $grammar Grammar for identifier escaping and subquery building.
     * @return ExprInterface The rendered expression (e.g., ""col" = :param" or "("c1", "c2") = (:v1, :v2)").
     * @complexity 5
     * STRUCTURE: ◇ target is string? → escape + ' = ' + ValueBuilder | ◇ array → '(' + escape each + ') = ' + ◇ MaybeReturnable → buildMaybeReturnableQuery | ◇ array → ValueBuilder.build | else → ValueBuilder.build
     */
    public function getSql(GrammarInterface $grammar): ExprInterface
    {
        $vb = new ValueBuilder();

        if (is_string($this->target)) {
            $escaped = $grammar->escapeIdentifier($this->target);
            $expr = $vb->build($this->value, $grammar);

            return new Expr($escaped . ' = ' . $expr->getExpression($grammar), $expr->getParams());
        }

        $escapedTargets = array_map(
            fn(string $t): string => $grammar->escapeIdentifier($t),
            $this->target,
        );
        $targetPart = '(' . implode(', ', $escapedTargets) . ')';

        if ($this->value instanceof MaybeReturnableQueryInterface) {
            $bq = $grammar->buildMaybeReturnableQuery($this->value);

            return new Expr($targetPart . ' = (' . $bq->sql . ')', $bq->params);
        }

        $expr = $vb->build($this->value, $grammar);

        return new Expr($targetPart . ' = ' . $expr->getExpression($grammar), $expr->getParams());
    }
    // endregion METHOD_getSql
}
// endregion CLASS_SetClause
