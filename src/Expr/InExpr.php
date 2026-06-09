<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(7): Expression; CONCEPT(6): Comparison; TECH(7): IN]
/**
 * @moduleContract
 * @purpose IN operator expression — checks if a value is in a set or subquery.
 * @scope WHERE conditions with IN operator.
 * @input left expression, right expression (array or subquery)
 * @output IN SQL expression
 * @modulemap
 * InExpr => IN operator expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: InExpr, IN operator, subquery condition
// STRUCTURE: ▶ ┌left, right┐ → parent::__construct(left, 'IN', right) → ∑ [InExpr]

// region CLASS_InExpr [DOMAIN(7): Expression; CONCEPT(6): Comparison; TECH(7): IN]
class InExpr extends OpExpr
{
    // region METHOD___construct [DOMAIN(7): Expression; CONCEPT(6): Comparison; TECH(7): IN]
    /**
     * @purpose Create an IN expression for set membership or subquery inclusion.
     *
     * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
     *
     * @phpstan-template TExpression of TValue|array<TExpression>
     *
     * @param TExpression                                           $left
     * @param array<TExpression>|ExprInterface|SelectQueryInterface $right
     */
    public function __construct(
        bool|int|float|string|ExprInterface|SelectQueryInterface|array|null $left,
        array|ExprInterface|SelectQueryInterface $right,
    ) {
        parent::__construct($left, 'IN', $right);
    }
    // endregion METHOD___construct
}
// endregion CLASS_InExpr
