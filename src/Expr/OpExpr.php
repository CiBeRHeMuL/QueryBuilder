<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(7): Expression; CONCEPT(7): Operator; TECH(7): Binary]
/**
 * @moduleContract
 * @purpose Binary operator expression — builds SQL like `left OPERATOR right` with automatic IS substitution for NULL/boolean equality.
 * @scope Comparison and arithmetic operations.
 * @input left, operator string, right
 * @output Binary operator SQL expression
 * @invariants
 * - `= NULL` is automatically converted to `IS NULL`.
 * - `= TRUE/FALSE` is converted to `IS TRUE/FALSE`.
 * - Sub-expressions are parenthesized when needed.
 * @modulemap
 * OpExpr => Binary operator expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: OpExpr, binary operator, comparison expression

// region CLASS_OpExpr [DOMAIN(7): Expression; CONCEPT(7): Operator; TECH(7): Binary]
class OpExpr extends AbstractExpr
{
    // region METHOD___construct [DOMAIN(7): Expression; CONCEPT(6): Init; TECH(7): Operator]
    /**
     * @purpose Store operands and operator. Auto-converts `= NULL` to `IS NULL` and `= TRUE/FALSE` to `IS TRUE/FALSE`.
     * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
     * @phpstan-template TExpression of TValue|array<TExpression>
     *
     * @param TExpression $left
     * @param string $operator
     * @param TExpression $right
     */
    public function __construct(
        private bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|array|null $left,
        private string $operator,
        private bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|array|null $right,
    ) {
        // Change operator to IS if right expr is null, true or false (to prevent a = null expressions).
        // TODO am i need to do it here??
        if ($this->operator === '=' && in_array($this->right, [null, true, false], true)) {
            $this->operator = 'IS';
        }
    }
    // endregion METHOD___construct

    // region METHOD_doBuild [DOMAIN(7): Expression; CONCEPT(7): Build; TECH(7): Compilation]
    /**
     * @purpose Build the binary operator SQL expression with automatic parenthesization of sub-expressions.
     * @io left, operator, right, GrammarInterface -> [string, array]
     * @complexity 6
     * @using ValueBuilder
     */
    protected function doBuild(GrammarInterface $grammar): array
    {
        $vb = new ValueBuilder();
        $left = $vb->build($this->left, $grammar);
        $right = $vb->build($this->right, $grammar);

        $shouldParenthesizeLeft = $this->left instanceof ExprInterface;
        $shouldParenthesizeRight = $this->right instanceof ExprInterface;

        $expr = sprintf(
            '%s%s%s %s %s%s%s',
            $shouldParenthesizeLeft ? '(' : '',
            $left->getExpression($grammar),
            $shouldParenthesizeLeft ? ')' : '',
            $this->operator,
            $shouldParenthesizeRight ? '(' : '',
            $right->getExpression($grammar),
            $shouldParenthesizeRight ? ')' : '',
        );
        $params ??= array_merge(
            $left->getParams(),
            $right->getParams(),
        );

        return [$expr, $params];
    }
    // endregion METHOD_doBuild
}
// endregion CLASS_OpExpr
