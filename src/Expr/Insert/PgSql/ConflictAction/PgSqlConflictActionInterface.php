<?php

namespace AndrewGos\QueryBuilder\Expr\Insert\Pg\ConflictAction;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

interface PgSqlConflictActionInterface
{
    public function getSql(GrammarInterface $grammar): ExprInterface;
}
