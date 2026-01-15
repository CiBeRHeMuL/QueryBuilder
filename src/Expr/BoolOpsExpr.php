<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @phpstan-template TCondition of TValue|array<TCondition>
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, bool|ExprInterface>
 */
class BoolOpsExpr extends AbstractExpr
{
    /**
     * @param TConditions $conditions
     * @param string $operator
     */
    public function __construct(
        private array $conditions,
        private string $operator,
    ) {}

    protected function doBuild(GrammarInterface $grammar): array
    {
        $conditions = HExpr::normalizeConditions($this->conditions, $grammar);

        $expressions = [];
        $params = [];
        if (count($conditions) > 1) {
            foreach ($conditions as $condition) {
                $expressions[] = sprintf(
                    '(%s)',
                    $condition->getExpression($grammar),
                );
                $params = HExpr::mergeParams($params, $condition->getParams());
            }
        } else {
            $condition = reset($conditions);
            $expressions[] = $condition->getExpression($grammar);
            $params = $condition->getParams();
        }

        return [implode(" $this->operator ", $expressions), $params];
    }
}
