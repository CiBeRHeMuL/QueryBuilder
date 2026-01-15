<?php

namespace AndrewGos\QueryBuilder\Expr\Insert\Pg\ConflictAction;

use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

readonly class MultiColumn
{
    public function __construct(
        protected(set) array $columnNames,
        protected(set) array|SelectQueryInterface $value,
    ) {}
}
