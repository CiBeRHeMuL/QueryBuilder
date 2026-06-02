<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(6): Expression; CONCEPT(5): Default; TECH(6): DDL]
/**
 * @moduleContract
 * @purpose Represents the SQL DEFAULT keyword for INSERT/UPDATE/MERGE statements.
 * @scope DDL value expression.
 * @input none
 * @output 'DEFAULT' string, empty params
 * @modulemap
 * DefaultValue => SQL DEFAULT keyword expression
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: DefaultValue, SQL DEFAULT, DDL expression

// region CLASS_DefaultValue [DOMAIN(6): Expression; CONCEPT(5): Default; TECH(6): DDL]
/**
 * @purpose This expression can be used to specify that value in INSERT, UPDATE, MERGE statements should be set to DEFAULT value
 */
class DefaultValue implements ExprInterface
{
    /**
     * @inheritDoc
     */
    public function getExpression(GrammarInterface $grammar): string
    {
        return 'DEFAULT';
    }

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        return [];
    }
}
// endregion CLASS_DefaultValue
