<?php

namespace AndrewGos\QueryBuilder\Builder;

use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Literal;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use BackedEnum;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Builder; CONCEPT(8): ValueBuilding; TECH(8): TypeDispatch]
/**
 * @moduleContract
 * @purpose Dispatches values of various types (scalars, enums, expressions, queries, arrays) into ExprInterface instances.
 * @scope Value normalization, type-based dispatch, sub-query building.
 * @input mixed value, GrammarInterface, bool $stringAsIdentifier
 * @output ExprInterface
 * @invariants
 * - Every call to build() pre-validates via HExpr::testExpr().
 * - Sub-queries are wrapped in parentheses.
 * @modulemap
 * ValueBuilder => Value-to-expression dispatcher
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: ValueBuilder, value dispatch, type conversion, expression builder

// region CLASS_ValueBuilder [DOMAIN(8): Builder; CONCEPT(8): ValueBuilding; TECH(8): TypeDispatch]
final readonly class ValueBuilder
{
    // region METHOD_build [DOMAIN(8): Builder; CONCEPT(8): EntryPoint; TECH(8): Validation]
    /**
     * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
     * @phpstan-template TExpression of TValue|array<TExpression>
     *
     * @param TExpression $value
     * @param GrammarInterface $grammar
     * @param bool $stringAsIdentifier
     *
     * @return ExprInterface
     * @purpose Validate and dispatch a value to the appropriate expression builder.
     * @io mixed value + GrammarInterface -> ExprInterface
     * @complexity 3
     * @uses HExpr::testExpr
     */
    public function build(
        bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null $value,
        GrammarInterface $grammar,
        bool $stringAsIdentifier = false,
    ): ExprInterface {
        HExpr::testExpr($value);

        return $this->doBuild($value, $grammar, $stringAsIdentifier);
    }
    // endregion METHOD_build

    // region METHOD_doBuild [DOMAIN(8): Builder; CONCEPT(8): Dispatch; TECH(8): Match]
    /**
     * @purpose Internal type-based dispatch — select the correct builder for each value type.
     * @io mixed value -> ExprInterface
     * @complexity 7
     */
    private function doBuild(
        bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null $value,
        GrammarInterface $grammar,
        bool $stringAsIdentifier = false,
    ): ExprInterface {
        return match (true) {
            $value instanceof SelectQueryInterface => $this->buildSelectQuery($value, $grammar),
            $value instanceof ValuesQueryInterface => $this->buildValuesQuery($value, $grammar),
            $value instanceof ExprInterface => $value,
            $value instanceof UnitEnum => $this->buildEnum($value),
            is_null($value) => new Expr('NULL'),
            is_bool($value) => new Expr($value ? 'TRUE' : 'FALSE'),
            is_array($value) => $this->buildArray($value, $grammar, $stringAsIdentifier),
            $stringAsIdentifier && is_string($value) => new Expr($grammar->escapeIdentifierDotted($value)),
            default => new Literal($value),
        };
    }
    // endregion METHOD_doBuild

    // region METHOD_buildSelectQuery [DOMAIN(8): Builder; CONCEPT(7): SubQuery; TECH(8): SELECT]
    /**
     * @purpose Wrap a SelectQuery in parentheses for use as a sub-expression.
     * @io SelectQueryInterface + GrammarInterface -> ExprInterface
     * @complexity 3
     */
    private function buildSelectQuery(SelectQueryInterface $query, GrammarInterface $grammar): ExprInterface
    {
        $bq = $grammar->buildSelectQuery($query);
        return new Expr(
            "($bq->sql)",
            $bq->params,
        );
    }
    // endregion METHOD_buildSelectQuery

    // region METHOD_buildValuesQuery [DOMAIN(8): Builder; CONCEPT(7): SubQuery; TECH(8): VALUES]
    /**
     * @purpose Wrap a ValuesQuery in parentheses for use as a sub-expression.
     * @io ValuesQueryInterface + GrammarInterface -> ExprInterface
     * @complexity 3
     */
    private function buildValuesQuery(ValuesQueryInterface $query, GrammarInterface $grammar): ExprInterface
    {
        $bq = $grammar->buildValuesQuery($query);
        return new Expr(
            "($bq->sql)",
            $bq->params,
        );
    }
    // endregion METHOD_buildValuesQuery

    // region METHOD_buildArray [DOMAIN(8): Builder; CONCEPT(7): Array; TECH(7): Recursion]
    /**
     * @purpose Recursively build each array element and combine into a parenthesized, comma-separated expression.
     * @io array -> ExprInterface
     * @complexity 6
     */
    private function buildArray(
        array $value,
        GrammarInterface $grammar,
        bool $stringAsIdentifier = false,
    ): ExprInterface {
        $builtExpressions = array_map(
            fn($e) => $this->doBuild($e, $grammar, $stringAsIdentifier),
            $value,
        );

        $expressions = [];
        $params = [];
        foreach ($builtExpressions as $expr) {
            $expressions[] = $expr->getExpression($grammar);
            $params = HExpr::mergeParams($params, $expr->getParams());
        }

        $expr = sprintf(
            '(%s)',
            implode(
                ', ',
                $expressions,
            ),
        );

        return new Expr(
            $expr,
            $params,
        );
    }
    // endregion METHOD_buildArray

    // region METHOD_buildEnum [DOMAIN(7): Builder; CONCEPT(6): Enum; TECH(7): BackedEnum]
    /**
     * @purpose Convert a UnitEnum to a named parameter expression.
     * @io UnitEnum -> ExprInterface
     * @complexity 3
     */
    private function buildEnum(UnitEnum $value): ExprInterface
    {
        $paramId = $this->generateParamId();

        return new Expr(
            sprintf(':%s', $paramId),
            [$paramId => $value instanceof BackedEnum ? $value->value : $value->name],
        );
    }
    // endregion METHOD_buildEnum

    // region METHOD_generateParamId [DOMAIN(6): Builder; CONCEPT(5): Params; TECH(6): ID]
    /**
     * @purpose Generate a unique parameter identifier for named parameter binding.
     * @io -> string
     * @complexity 2
     */
    private function generateParamId(): string
    {
        static $n = 0;
        $oid = spl_object_id($this);

        return sprintf('v%s_%s', $oid, ++$n);
    }
    // endregion METHOD_generateParamId
}
// endregion CLASS_ValueBuilder
