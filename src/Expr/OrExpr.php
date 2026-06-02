<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(7): Expression; CONCEPT(6): Boolean; TECH(7): OR]
/**
 * @moduleContract
 * @purpose OR boolean expression — joins conditions with OR operator.
 * @scope WHERE/HAVING condition grouping.
 * @input array of conditions
 * @output OR-joined SQL expression
 * @modulemap
 * OrExpr => OR boolean expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: OrExpr, OR expression, boolean condition

// region CLASS_OrExpr [DOMAIN(7): Expression; CONCEPT(6): Boolean; TECH(7): OR]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @phpstan-template TCondition of TValue|array<TCondition>
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, bool|ExprInterface>
 */
class OrExpr extends BoolOpsExpr
{
    // region METHOD___construct [DOMAIN(7): Expression; CONCEPT(6): Init; TECH(6): Constructor]
    /**
     * @purpose Create an OR expression with the given conditions.
     * @param TConditions $conditions
     */
    public function __construct(
        array $conditions,
    ) {
        parent::__construct($conditions, 'OR');
    }
    // endregion METHOD___construct
}
// endregion CLASS_OrExpr
