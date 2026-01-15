<?php

namespace AndrewGos\QueryBuilder\Expr\Table\PgSql;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

class PgSqlSelectTable extends SelectTable
{
    public function __construct(
        string $name,
        protected(set) bool $only = false,
    ) {
        parent::__construct($name);
    }
}
