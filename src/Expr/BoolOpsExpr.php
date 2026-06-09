<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(7): Expression; CONCEPT(7): Boolean; TECH(7): Abstract]
/**
 * @moduleContract
 * @purpose Abstract base for boolean operator expressions (AND, OR) with condition normalization.
 * @scope Boolean condition grouping.
 * @input array of conditions, string operator
 * @output Operator-joined SQL expressions
 * @invariants
 * - Multiple conditions are parenthesized when joined.
 * - Single condition is rendered without parentheses.
 * @modulemap
 * BoolOpsExpr => Abstract boolean expression base
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: BoolOpsExpr, boolean expression, AND OR base
// STRUCTURE: ▶ doBuild ┌conditions + operator┐ → ⚡ HExpr::normalizeConditions → ◇ count > 1? ○ each: wrap in (parens) └─ else → single unwrapped → ⊕ implode(operator) → ∑ return [sql, params]

// region CLASS_BoolOpsExpr [DOMAIN(7): Expression; CONCEPT(7): Boolean; TECH(7): Abstract]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 *
 * @phpstan-template TCondition of TValue|array<TCondition>
 *
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, bool|ExprInterface>
 */
class BoolOpsExpr extends AbstractExpr
{
    // region METHOD___construct [DOMAIN(7): Expression; CONCEPT(6): Init; TECH(6): Constructor]
    /**
     * @purpose Store conditions and operator for later building.
     *
     * @param TConditions $conditions
     * @param string      $operator
     */
    public function __construct(
        private array $conditions,
        private string $operator,
    ) {}
    // endregion METHOD___construct

    // region METHOD_doBuild [DOMAIN(7): Expression; CONCEPT(7): Build; TECH(7): Normalization]
    /**
     * @purpose Normalize conditions and join them with the boolean operator. Multiple conditions are parenthesized.
     * @io array conditions, GrammarInterface -> [string, array]
     * @complexity 5
     */
    protected function doBuild(GrammarInterface $grammar): array
    {
        $conditions = HExpr::normalizeConditions($this->conditions, $grammar);

        if (empty($conditions)) {
            throw QueryBuilderException::emptyBoolExpression();
        }

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
    // endregion METHOD_doBuild
}
// endregion CLASS_BoolOpsExpr
