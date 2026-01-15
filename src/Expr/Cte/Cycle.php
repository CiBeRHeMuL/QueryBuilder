<?php

namespace AndrewGos\QueryBuilder\Expr\Cte;

use AndrewGos\QueryBuilder\Expr\Literal;

final readonly class Cycle
{
    /**
     * @param string[] $columns
     * @param string $cycleMarkColumnName
     * @param string $cyclePathColumnName
     * @param Literal $cycleMarkValue
     * @param Literal $cycleMarkDefault
     */
    public function __construct(
        private(set) array $columns,
        private(set) string $cycleMarkColumnName,
        private(set) string $cyclePathColumnName,
        private(set) Literal $cycleMarkValue = new Literal(true),
        private(set) Literal $cycleMarkDefault = new Literal(false),
    ) {}
}
