<?php

namespace AndrewGos\QueryBuilder\Expr\Insert\Pg\ConflictAction;

use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

class DoNothing implements PgSqlConflictActionInterface
{
    public function getSql(GrammarInterface $grammar): ExprInterface
    {
        return new Expr('DO NOTHING');
    }
}
