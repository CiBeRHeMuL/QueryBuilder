<?php

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
    /**
     * Checks that value can be used as an expression
     *
     * @param mixed|TExpression $value
     *
     * @throws QueryBuilderException
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

    /**
     * Checks that value can be used as a select expression
     *
     * @param mixed|TExpression $value
     *
     * @throws QueryBuilderException
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

    /**
     * Checks that value can be used as a group by expression
     *
     * @param mixed|TGroupExpression $value
     *
     * @throws QueryBuilderException
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

    /**
     * Checks that value can be used as a condition
     *
     * @param mixed|TCondition $condition
     *
     * @return void
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

    /**
     * Checks that value can be used as a condition
     *
     * @param mixed|TStandaloneCondition $condition
     *
     * @return void
     */
    public static function testStandaloneCondition(mixed $condition): void
    {
        $isCondition = is_bool($condition)
            || $condition instanceof ExprInterface;

        $isCondition === false && throw QueryBuilderException::valueIsNotStandaloneCondition($condition);
    }

    /**
     * @param array|TConditions $conditions
     *
     * @return void
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

    /**
     * Checks that value can be used as an expression
     *
     * @param mixed|TTable $value
     *
     * @throws QueryBuilderException
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

    /**
     * @param TConditions $conditions
     * @param GrammarInterface $grammar
     *
     * @return ExprInterface[]
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

    /**
     * @param TOrderBy $columns
     *
     * @return OrderColumn[]
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

    /**
     * @param mixed|TTable $value
     *
     * @return TNormalizedTable
     *
     * @throws QueryBuilderException
     */
    public static function normalizeTable(mixed $value): SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface
    {
        self::testTable($value);

        return is_string($value) ? new SelectTable($value) : $value;
    }

    /**
     * @param (string|BuiltQuery|ExprInterface)[] $expressions
     * @param GrammarInterface $grammar
     * @param string $glue
     *
     * @return ExprInterface
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
}
