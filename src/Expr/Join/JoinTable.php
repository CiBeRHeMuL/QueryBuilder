<?php

namespace AndrewGos\QueryBuilder\Expr\Join;

use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface as SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

final readonly class JoinTable
{
    /**
     * @param JoinTypeEnum $type
     * @param SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface $table
     * @param ExprInterface[] $conditions
     * @param bool $naturalJoin
     */
    public function __construct(
        private(set) JoinTypeEnum $type,
        private(set) SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface $table,
        private(set) array $conditions = [],
        private(set) bool $naturalJoin = false,
    ) {}
}
