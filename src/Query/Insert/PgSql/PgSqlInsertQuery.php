<?php

namespace AndrewGos\QueryBuilder\Query\Insert\PgSql;

use AndrewGos\QueryBuilder\Enum\Insert\PgSql\PgSqlOverrideValueMethodEnum;
use AndrewGos\QueryBuilder\Query\Insert\InsertQuery;
use AndrewGos\QueryBuilder\Query\Interface\PgSql\ReturningInterface;
use AndrewGos\QueryBuilder\Query\Trait\PgSql\ReturningTrait;

class PgSqlInsertQuery extends InsertQuery implements ReturningInterface
{
    use ReturningTrait;

    protected(set) ?PgSqlOverrideValueMethodEnum $overrideValueMethod = null;
}
