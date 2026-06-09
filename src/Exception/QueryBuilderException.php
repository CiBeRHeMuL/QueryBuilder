<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Exception;

use AndrewGos\Helpers\HString;
use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Order\OrderColumn;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use RuntimeException;

// region MODULE_CONTRACT [DOMAIN(9): Exception; CONCEPT(9): ErrorHandling; TECH(9): Exception]
/**
 * @moduleContract
 * @purpose Centralized exception factory for the QueryBuilder domain. Provides static named constructors for all error types.
 * @scope Error handling, validation failures, domain-specific exceptions.
 * @input Error context (values, types, objects)
 * @output QueryBuilderException instances
 * @invariants
 * - All exceptions extend RuntimeException.
 * - Messages contain typed context for debugging.
 * @modulemap
 * QueryBuilderException => Domain exception factory
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: QueryBuilderException, exception, error handling, validation
// STRUCTURE: ▶ static named constructors ┌valueIsNotExpr, valueIsNotTable, ... (10 total)┐ → ○ each: ⚡ format message with typed context → ⊕ new self → ∑ return QueryBuilderException

// region CLASS_QueryBuilderException [DOMAIN(9): Exception; CONCEPT(9): ErrorHandling; TECH(9): Exception]
class QueryBuilderException extends \RuntimeException
{
    // region METHOD_valueIsNotExpr [DOMAIN(9): Exception; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Create exception for invalid expression values — value is not of the allowed types.
     * @io mixed -> self
     * @complexity 2
     */
    public static function valueIsNotExpr(mixed $value): self
    {
        return new self(
            sprintf(
                <<<TEXT
                    Value "%s" is not a valid expression.
                    Valid expression value MUST be of type bool|int|float|string|%s|%s|%s|%s|null or an array with items of this type on any nested level
                    TEXT,
                HString::stringifyValue($value),
                ExprInterface::class,
                SelectQueryInterface::class,
                ValuesQueryInterface::class,
                \UnitEnum::class,
            ),
        );
    }
    // endregion METHOD_valueIsNotExpr

    // region METHOD_valueIsNotTable [DOMAIN(9): Exception; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Create exception for invalid table expressions.
     * @io mixed -> self
     * @complexity 2
     */
    public static function valueIsNotTable(mixed $value): self
    {
        return new self(
            sprintf(
                <<<TEXT
                    Value "%s" is not a valid table expression.
                    Valid table expression value MUST be of type string|%s|%s|%s|%s
                    TEXT,
                HString::stringifyValue($value),
                ExprInterface::class,
                SelectQueryInterface::class,
                ValuesQueryInterface::class,
                SelectTable::class,
            ),
        );
    }
    // endregion METHOD_valueIsNotTable

    // region METHOD_valueIsNotSelectExpr [DOMAIN(9): Exception; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Create exception for invalid SELECT expression values.
     * @io mixed -> self
     * @complexity 2
     */
    public static function valueIsNotSelectExpr(mixed $value): self
    {
        return new self(
            sprintf(
                <<<TEXT
                    Value "%s" is not a valid select expression.
                    Valid select expression value MUST be of type bool|int|float|string|%s|%s|%s|%s|null or an array with items of this type on any
                    nested level
                    TEXT,
                HString::stringifyValue($value),
                ExprInterface::class,
                SelectQueryInterface::class,
                ValuesQueryInterface::class,
                \UnitEnum::class,
            ),
        );
    }
    // endregion METHOD_valueIsNotSelectExpr

    // region METHOD_valueIsNotGroupByExpr [DOMAIN(9): Exception; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Create exception for invalid GROUP BY expression values.
     * @io mixed -> self
     * @complexity 2
     */
    public static function valueIsNotGroupByExpr(mixed $value): self
    {
        return new self(
            sprintf(
                <<<TEXT
                    Value "%s" is not a valid group by expression.
                    Valid group by expression value MUST be of type bool|int|float|string|%s|%s|null or an array with items of this type on any nested level
                    TEXT,
                HString::stringifyValue($value),
                ExprInterface::class,
                \UnitEnum::class,
            ),
        );
    }
    // endregion METHOD_valueIsNotGroupByExpr

    // region METHOD_valueIsNotCondition [DOMAIN(9): Exception; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Create exception for invalid condition values.
     * @io mixed -> self
     * @complexity 2
     */
    public static function valueIsNotCondition(mixed $value): self
    {
        return new self(
            sprintf(
                <<<TEXT
                    Value "%s" is not a valid condition.
                    Valid condition value MUST be of type bool|int|float|string|%s|%s|%s|%s|null or an array with items of this type on any nested level
                    TEXT,
                HString::stringifyValue($value),
                ExprInterface::class,
                SelectQueryInterface::class,
                ValuesQueryInterface::class,
                \UnitEnum::class,
            ),
        );
    }
    // endregion METHOD_valueIsNotCondition

    // region METHOD_valueIsNotStandaloneCondition [DOMAIN(9): Exception; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Create exception for invalid standalone condition values (must be bool or ExprInterface).
     * @io mixed -> self
     * @complexity 2
     */
    public static function valueIsNotStandaloneCondition(mixed $value): self
    {
        return new self(
            sprintf(
                <<<TEXT
                    Value "%s" is not a valid standalone condition.
                    Valid standalone condition value MUST be of type bool|%s
                    TEXT,
                HString::stringifyValue($value),
                ExprInterface::class,
            ),
        );
    }
    // endregion METHOD_valueIsNotStandaloneCondition

    // region METHOD_valueIsNotOrderBy [DOMAIN(9): Exception; CONCEPT(8): Validation; TECH(8): TypeCheck]
    /**
     * @purpose Create exception for invalid ORDER BY expression values.
     * @io int|string key, mixed value -> self
     * @complexity 2
     */
    public static function valueIsNotOrderBy(int|string $key, mixed $value): self
    {
        return new self(
            sprintf(
                <<<'TEXT'
                    Value "%1$s" is not a valid order by expression.
                    Valid order by expression value MUST be of type int|string if "%2$s" is string
                    or int|string|%3$s|%4$s if "%2$s" is int
                    TEXT,
                HString::stringifyValue($value),
                HString::stringifyValue($key),
                ExprInterface::class,
                OrderColumn::class,
            ),
        );
    }
    // endregion METHOD_valueIsNotOrderBy

    // region METHOD_exprNotBuilt [DOMAIN(9): Exception; CONCEPT(7): Lifecycle; TECH(7): LazyBuild]
    /**
     * @purpose Create exception for accessing params on an expression that hasn't been built yet.
     * @io ExprInterface -> self
     * @complexity 1
     */
    public static function exprNotBuilt(ExprInterface $expr): self
    {
        return new self(
            sprintf(
                <<<'TEXT'
                    Expression "%s" (oid "%s") is not built yet.
                    Call "getExpression()" method before using it.
                    TEXT,
                $expr::class,
                spl_object_id($expr),
            ),
        );
    }
    // endregion METHOD_exprNotBuilt

    // region METHOD_invalidIdentifier [DOMAIN(9): Exception; CONCEPT(7): Validation; TECH(8): Grammar]
    /**
     * @purpose Create exception for identifiers that don't pass grammar validation.
     * @io string identifier, GrammarInterface -> self
     * @complexity 1
     */
    public static function invalidIdentifier(string $identifier, GrammarInterface $grammar): self
    {
        return new self(
            sprintf(
                <<<'TEXT'
                    Identifier "%s" is invalid for grammar "%s".
                    For more information see "checkIdentifier()" method of grammar.
                    TEXT,
                $identifier,
                $grammar::class,
            ),
        );
    }
    // endregion METHOD_invalidIdentifier

    // region METHOD_invalidNaturalJoinType [DOMAIN(9): Exception; CONCEPT(7): Validation; TECH(7): Join]
    /**
     * @purpose Create exception when a join type is incompatible with NATURAL join.
     * @io JoinTypeEnum -> self
     * @complexity 1
     */
    public static function invalidNaturalJoinType(JoinTypeEnum $type): self
    {
        return new self(
            sprintf(
                <<<'TEXT'
                    Cannot use join type "%s" with natural join.
                    TEXT,
                $type->getSql(),
            ),
        );
    }
    // endregion METHOD_invalidNaturalJoinType

    // region METHOD_valueIsNotReturnableQuery [DOMAIN(9): Exception; CONCEPT(8): Validation; TECH(8): Query]
    /**
     * @purpose Create exception when a query does not implement returnable interface.
     * @io mixed query -> self
     * @complexity 1
     */
    public static function valueIsNotReturnableQuery(mixed $query): self
    {
        return new self(
            sprintf(
                <<<'TEXT'
                    Value "%s" is not returnable query.
                    To be returnable query MUST implements "%s" interface and returns TRUE from "isReturnable()" method
                    TEXT,
                HString::stringifyValue($query),
                MaybeReturnableQueryInterface::class,
            ),
        );
    }
    // endregion METHOD_valueIsNotReturnableQuery

    // region METHOD_emptyBoolExpression [DOMAIN(9): Exception; CONCEPT(7): Validation; TECH(8): BoolOps]
    /**
     * @purpose Create exception when a boolean expression (AND/OR) has no conditions.
     * @io -> self
     * @complexity 1
     */
    public static function emptyBoolExpression(): self
    {
        return new self(
            'AND/OR expression requires at least one condition. Ensure where()/having() is called before orWhere()/orHaving().',
        );
    }
    // endregion METHOD_emptyBoolExpression

    // region METHOD_returnableQueryCannotBeBuilt [DOMAIN(9): Exception; CONCEPT(8): Validation; TECH(8): Grammar]
    /**
     * @purpose Create exception when a returnable query cannot be built by the given grammar.
     * @io MaybeReturnableQueryInterface, GrammarInterface -> self
     * @complexity 1
     */
    public static function returnableQueryCannotBeBuilt(MaybeReturnableQueryInterface $query, GrammarInterface $grammar): self
    {
        return new self(
            sprintf(
                <<<'TEXT'
                    Maybe returnable query "%s" cannot be built by grammar "%s"
                    TEXT,
                $query::class,
                $grammar::class,
            ),
        );
    }
    // endregion METHOD_returnableQueryCannotBeBuilt
}
// endregion CLASS_QueryBuilderException
