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

final readonly class ValueBuilder
{
    /**
     * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
     * @phpstan-template TExpression of TValue|array<TExpression>
     *
     * @param TExpression $value
     * @param GrammarInterface $grammar
     * @param bool $stringAsIdentifier
     *
     * @return ExprInterface
     */
    public function build(
        bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null $value,
        GrammarInterface $grammar,
        bool $stringAsIdentifier = false,
    ): ExprInterface {
        HExpr::testExpr($value);

        return $this->doBuild($value, $grammar, $stringAsIdentifier);
    }

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

    private function buildSelectQuery(SelectQueryInterface $query, GrammarInterface $grammar): ExprInterface
    {
        $bq = $grammar->buildSelectQuery($query);
        return new Expr(
            "($bq->sql)",
            $bq->params,
        );
    }

    private function buildValuesQuery(ValuesQueryInterface $query, GrammarInterface $grammar): ExprInterface
    {
        $bq = $grammar->buildValuesQuery($query);
        return new Expr(
            "($bq->sql)",
            $bq->params,
        );
    }

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

    private function buildEnum(UnitEnum $value): ExprInterface
    {
        $paramId = $this->generateParamId();

        return new Expr(
            sprintf(':%s', $paramId),
            [$paramId => $value instanceof BackedEnum ? $value->value : $value->name],
        );
    }

    private function generateParamId(): string
    {
        static $n = 0;
        $oid = spl_object_id($this);

        return sprintf('v%s_%s', $oid, ++$n);
    }
}
