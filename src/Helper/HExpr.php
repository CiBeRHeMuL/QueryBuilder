<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Helper;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\InExpr;
use AndrewGos\QueryBuilder\Expr\OpExpr;
use AndrewGos\QueryBuilder\Expr\Order\OrderColumn;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Grammar\BuiltQuery;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface as SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(9): Helper; CONCEPT(9): ExpressionUtilities; TECH(9): StaticHelpers]
/**
 * @moduleContract
 * @purpose Static utility class providing validation, normalization, and merging operations for SQL expression nodes.
 * @scope Type validation, condition/order-by normalization, params merging, expression part merging.
 * @input Mixed values, arrays, GrammarInterface
 * @output Validated/normalized expressions, merged params, ExprInterface
 * @invariants
 * - All test* methods throw QueryBuilderException on invalid input.
 * - All normalize* methods preserve the original input structure.
 * - mergeParams supports both numeric and associative arrays.
 * @rationale
 * Q: Why are all methods static?
 * A: HExpr is a stateless utility namespace. Its methods are pure functions operating on input data.
 * @modulemap
 * HExpr => Expression utility class
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: HExpr, helper, expression utilities, validation, normalization

// region CLASS_HExpr [DOMAIN(9): Helper; CONCEPT(9): ExpressionUtilities; TECH(9): StaticHelpers]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|null
 * @template TExpression of TValue|array<TExpression>
 *
 * @template TSimpleValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TValues of TSimpleValue|TValues[]
 *
 * @template TSelectExpression of TExpression
 *
 * @template TGroupValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TGroupExpression of TGroupValue|array<TGroupExpression>
 *
 * @template TTable of string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable
 * @template TNormalizedTable of ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable
 *
 * @template TCondition of TValue|array<TCondition>
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, bool|ExprInterface>
 *
 * @template TOrderBy of array<string, int|string>|array<int, string|ExprInterface|OrderColumn> column => order, expression or OrderColumn
 */
class HExpr
{
    // region METHOD_testExpr [DOMAIN(9): Helper; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Checks that value can be used as an expression. Throws on invalid type.
     * @io mixed -> void (throws on invalid)
     * @complexity 5
     * @throws QueryBuilderException
     * @using QueryBuilderException::valueIsNotExpr
     * STRUCTURE: ▼ [value] → ◇ is_null/bool/int/float/string/ExprInterface/SelectQueryInterface/ValuesQueryInterface/UnitEnum/array → ● valid → ∑ return | ✗ throw
     */
    public static function testExpr(mixed $value): void
    {
        $isExpr = is_null($value)
            || is_bool($value)
            || is_int($value)
            || is_float($value)
            || is_string($value)
            || $value instanceof ExprInterface
            || $value instanceof SelectQueryInterface
            || $value instanceof ValuesQueryInterface
            || $value instanceof UnitEnum
            || is_array($value) && array_walk($value, self::testExpr(...));

        $isExpr === false && throw QueryBuilderException::valueIsNotExpr($value);
    }
    // endregion METHOD_testExpr

    // region METHOD_testSelectExpr [DOMAIN(9): Helper; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Checks that value can be used as a SELECT expression. Throws on invalid type.
     * @io mixed -> void (throws on invalid)
     * @complexity 5
     * @throws QueryBuilderException
     * @using QueryBuilderException::valueIsNotSelectExpr
     */
    public static function testSelectExpr(mixed $value): void
    {
        $isExpr = is_null($value)
            || is_bool($value)
            || is_int($value)
            || is_float($value)
            || is_string($value)
            || $value instanceof ExprInterface
            || $value instanceof SelectQueryInterface
            || $value instanceof ValuesQueryInterface
            || $value instanceof UnitEnum
            || is_array($value) && array_walk($value, self::testSelectExpr(...));

        $isExpr === false && throw QueryBuilderException::valueIsNotSelectExpr($value);
    }
    // endregion METHOD_testSelectExpr

    // region METHOD_testGroupByExpr [DOMAIN(9): Helper; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Checks that value can be used as a GROUP BY expression. Throws on invalid type.
     * @io mixed -> void (throws on invalid)
     * @complexity 5
     * @throws QueryBuilderException
     * @using QueryBuilderException::valueIsNotGroupByExpr
     */
    public static function testGroupByExpr(mixed $value): void
    {
        $isExpr = is_null($value)
            || is_bool($value)
            || is_int($value)
            || is_float($value)
            || is_string($value)
            || $value instanceof ExprInterface
            || $value instanceof UnitEnum
            || is_array($value) && array_walk($value, self::testGroupByExpr(...));

        $isExpr === false && throw QueryBuilderException::valueIsNotGroupByExpr($value);
    }
    // endregion METHOD_testGroupByExpr

    // region METHOD_testCondition [DOMAIN(9): Helper; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Checks that value can be used as a condition. Throws on invalid type.
     * @io mixed -> void (throws on invalid)
     * @complexity 5
     * @throws QueryBuilderException
     * @using QueryBuilderException::valueIsNotCondition
     */
    public static function testCondition(mixed $condition): void
    {
        $isCondition = is_null($condition)
            || is_bool($condition)
            || is_int($condition)
            || is_float($condition)
            || is_string($condition)
            || $condition instanceof ExprInterface
            || $condition instanceof SelectQueryInterface
            || $condition instanceof ValuesQueryInterface
            || $condition instanceof UnitEnum
            || is_array($condition) && array_walk($condition, self::testCondition(...));

        $isCondition === false && throw QueryBuilderException::valueIsNotCondition($condition);
    }
    // endregion METHOD_testCondition

    // region METHOD_testStandaloneCondition [DOMAIN(9): Helper; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Checks that value can be used as a standalone condition (bool or ExprInterface only). Throws on invalid type.
     * @io mixed -> void (throws on invalid)
     * @complexity 3
     * @throws QueryBuilderException
     * @using QueryBuilderException::valueIsNotStandaloneCondition
     */
    public static function testStandaloneCondition(mixed $condition): void
    {
        $isCondition = is_bool($condition)
            || $condition instanceof ExprInterface;

        $isCondition === false && throw QueryBuilderException::valueIsNotStandaloneCondition($condition);
    }
    // endregion METHOD_testStandaloneCondition

    // region METHOD_testConditionsArray [DOMAIN(9): Helper; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Validate all entries in a conditions array, dispatching by key type (string key -> testCondition, int key -> testStandaloneCondition).
     * @io array -> void (throws on invalid)
     * @complexity 4
     */
    public static function testConditionsArray(array $conditions): void
    {
        foreach ($conditions as $key => $condition) {
            if (is_string($key)) {
                self::testCondition($condition);
            } else {
                self::testStandaloneCondition($condition);
            }
        }
    }
    // endregion METHOD_testConditionsArray

    // region METHOD_testOrderByArray [DOMAIN(9): Helper; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Validate an ORDER BY array structure — string keys expect int|string values, int keys expect string|ExprInterface|OrderColumn values.
     * @io array -> void (throws on invalid)
     * @complexity 5
     * @throws QueryBuilderException
     */
    public static function testOrderByArray(array $columns): void
    {
        foreach ($columns as $key => $column) {
            if (is_string($key)) {
                $isOrderBy = is_int($column)
                    || is_string($column);
            } else {
                $isOrderBy = is_string($column)
                    || $column instanceof ExprInterface
                    || $column instanceof OrderColumn;
            }
            $isOrderBy === false && throw QueryBuilderException::valueIsNotOrderBy($key, $column);
        }
    }
    // endregion METHOD_testOrderByArray

    // region METHOD_testTable [DOMAIN(9): Helper; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Checks that value can be used as a table expression. Throws on invalid type.
     * @io mixed -> void (throws on invalid)
     * @complexity 3
     * @throws QueryBuilderException
     * @using QueryBuilderException::valueIsNotTable
     */
    public static function testTable(mixed $value): void
    {
        $isExpr = is_string($value)
            || $value instanceof ExprInterface
            || $value instanceof SelectQueryInterface
            || $value instanceof ValuesQueryInterface
            || $value instanceof SelectTable;

        $isExpr === false && throw QueryBuilderException::valueIsNotTable($value);
    }
    // endregion METHOD_testTable

    // region METHOD_normalizeConditions [DOMAIN(9): Helper; CONCEPT(9): Normalization; TECH(9): ConditionShortSyntax]
    /**
     * @purpose Normalize a conditions array: string keys become OpExpr/InExpr, int keys pass through as-is.
     *          STRUCTURE: ┌conditions┐ → ○ foreach: 〈is_string(key)? T/F〉 → T: ◇ is_array|SelectQuery|ValuesQuery → InExpr | → OpExpr(=) → ⊕ result[] | F: ◇ is_bool → ValueBuilder | → ⊕ result[]
     * @param TConditions $conditions
     * @param GrammarInterface $grammar
     *
     * @return ExprInterface[]
     * @complexity 7
     */
    public static function normalizeConditions(array $conditions, GrammarInterface $grammar): array
    {
        self::testConditionsArray($conditions);

        $vb = new ValueBuilder();
        $result = [];
        foreach ($conditions as $key => $condition) {
            // Process short syntax
            // 'a' => 1
            // 'a' => 'asdf'
            // 'a' => [1, 2, 3]
            // 'a' => new PgQuery()->select([1])
            if (is_string($key)) {
                // Special short syntax for arrays and queries. Use IN operator
                $result[] = match (true) {
                    is_array($condition),
                    $condition instanceof SelectQueryInterface,
                    $condition instanceof ValuesQueryInterface => new InExpr(
                        new Expr($grammar->escapeIdentifierDotted($key)),
                        $condition,
                    ),
                    default => new OpExpr(
                        new Expr($grammar->escapeIdentifierDotted($key)),
                        '=',
                        $condition,
                    ),
                };
            } else {
                if (is_bool($condition)) {
                    $result[] = $vb->build($condition, $grammar);
                } else {
                    $result[] = $condition;
                }
            }
        }
        return $result;
    }
    // endregion METHOD_normalizeConditions

    // region METHOD_normalizeOrderBy [DOMAIN(9): Helper; CONCEPT(9): Normalization; TECH(9): OrderByShortSyntax]
    /**
     * @purpose Normalize an ORDER BY array: string keys + SORT_ASC/SORT_DESC -> OrderColumn, int keys + string/ExprInterface -> OrderColumn.
     *          STRUCTURE: ┌columns┐ → ○ foreach: 〈is_string(key)? T/F〉 → T: OrderColumn(key, match column) ⊕ result[] | F: ◇ OrderColumn → ⊕ | ◇ ExprInterface → OrderColumn(col, ASC) ⊕ | → OrderColumn(col) ⊕
     * @param TOrderBy $columns
     *
     * @return OrderColumn[]
     * @complexity 7
     */
    public static function normalizeOrderBy(array $columns): array
    {
        self::testOrderByArray($columns);

        $orderBy = [];
        foreach ($columns as $key => $column) {
            // Process short syntax
            // 'a' => SORT_ASC
            // 'a' => 'ASC'
            // 'a' => 'USING =' // FOR POSTGRES
            if (is_string($key)) {
                $orderBy[] = new OrderColumn(
                    $key,
                    match (true) {
                        $column === SORT_ASC => 'ASC',
                        $column === SORT_DESC => 'DESC',
                        default => $column,
                    },
                );
            } else {
                if ($column instanceof OrderColumn) {
                    $orderBy[] = $column;
                } elseif ($column instanceof ExprInterface) {
                    // Default ASC sorting with expressions
                    $orderBy[] = new OrderColumn(
                        $column,
                        'ASC',
                    );
                } else {
                    // Short syntax for 'a' => 'ASC'
                    $orderBy[] = new OrderColumn(
                        $column,
                    );
                }
            }
        }

        return $orderBy;
    }
    // endregion METHOD_normalizeOrderBy

    // region METHOD_mergeParams [DOMAIN(8): Helper; CONCEPT(8): Merge; TECH(8): Params]
    /**
     * @purpose Merge multiple parameter arrays, handling both numeric and associative keys. Numeric keys append, string keys overwrite.
     * @io array left, array ...right -> array
     * @complexity 4
     */
    public static function mergeParams(array $left, array ...$right): array
    {
        foreach ($right as $params) {
            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    $left[] = $value;
                } else {
                    $left[$key] = $value;
                }
            }
        }
        return $left;
    }
    // endregion METHOD_mergeParams

    // region METHOD_normalizeTable [DOMAIN(9): Helper; CONCEPT(8): Normalization; TECH(8): Table]
    /**
     * @purpose Normalize a table value: strings become SelectTable, other types pass through.
     * @io mixed -> SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface
     * @complexity 2
     */
    public static function normalizeTable(mixed $value): SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface
    {
        self::testTable($value);

        return is_string($value) ? new SelectTable($value) : $value;
    }
    // endregion METHOD_normalizeTable

    // region METHOD_mergeExpressionParts [DOMAIN(9): Helper; CONCEPT(9): Merge; TECH(9): PartJoining]
    /**
     * @purpose Merge an array of expression parts (ExprInterface, BuiltQuery, or string) into a single ExprInterface joined by a glue string.
     *          STRUCTURE: ┌expressions┐ → ○ foreach: 〈ExprInterface〉 → getExpression ⊕ params | 〈BuiltQuery〉 → sql ⊕ params | 〈string〉 → ⊕ parts → ∑ Expr(implode(glue, parts), params)
     * @param (string|BuiltQuery|ExprInterface)[] $expressions
     * @param GrammarInterface $grammar
     * @param string $glue
     *
     * @return ExprInterface
     * @complexity 6
     */
    public static function mergeExpressionParts(
        array $expressions,
        GrammarInterface $grammar,
        string $glue = ' ',
    ): ExprInterface {
        $parts = [];
        $params = [];

        foreach ($expressions as $expression) {
            if ($expression instanceof ExprInterface) {
                $parts[] = $expression->getExpression($grammar);
                $params = self::mergeParams($params, $expression->getParams());
            } elseif ($expression instanceof BuiltQuery) {
                $parts[] = $expression->sql;
                $params = self::mergeParams($params, $expression->params);
            } elseif (is_string($expression)) {
                $parts[] = $expression;
            }
        }

        return new Expr(
            implode($glue, $parts),
            $params,
        );
    }
    // endregion METHOD_mergeExpressionParts
}
// endregion CLASS_HExpr
