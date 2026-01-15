<?php

namespace AndrewGos\QueryBuilder\Expr\SetOperation;

use AndrewGos\QueryBuilder\Enum\SetOperationEnum;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

final readonly class SetOperation
{
    public function __construct(
        private(set) SetOperationEnum $operation,
        private(set) SelectQueryInterface $query,
    ) {}
}
