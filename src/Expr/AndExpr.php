<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(7): Expression; CONCEPT(6): Boolean; TECH(7): AND]
/**
 * @moduleContract
 * @purpose AND boolean expression — joins conditions with AND operator.
 * @scope WHERE/HAVING condition grouping.
 * @input array of conditions
 * @output AND-joined SQL expression
 * @modulemap
 * AndExpr => AND boolean expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: AndExpr, AND expression, boolean condition

// region CLASS_AndExpr [DOMAIN(7): Expression; CONCEPT(6): Boolean; TECH(7): AND]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @phpstan-template TCondition of SelectQueryInterface|UnitEnum
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of UnitEnum|array<int, bool|ExprInterface>
 */
class AndExpr extends BoolOpsExpr
{
    // region METHOD___construct [DOMAIN(7): Expression; CONCEPT(6): Init; TECH(6): Constructor]
    /**
     * @purpose Create an AND expression with the given conditions.
     * @param TConditions $conditions
     */
    public function __construct(
        array $conditions,
    ) {
        parent::__construct($conditions, 'AND');
    }
    // endregion METHOD___construct
}
// endregion CLASS_AndExpr
