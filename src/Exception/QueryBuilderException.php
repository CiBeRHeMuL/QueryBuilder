<?php

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
use UnitEnum;

class QueryBuilderException extends RuntimeException
{
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
                UnitEnum::class,
            ),
        );
    }

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
                UnitEnum::class,
            ),
        );
    }

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
                UnitEnum::class,
            ),
        );
    }

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
                UnitEnum::class,
            ),
        );
    }

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

    public static function returnableQueryCannotBeBuilt(MaybeReturnableQueryInterface $query, GrammarInterface $grammar): self
    {
        return new self(
            sprintf(
                <<<'TEXT'
                    Maybe returnable query "%s" cannot be built by grammar "%"
                    TEXT,
                $query::class,
                $grammar::class,
            ),
        );
    }
}
