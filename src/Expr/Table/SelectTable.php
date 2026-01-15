<?php

namespace AndrewGos\QueryBuilder\Expr\Table;

class SelectTable
{
    public function __construct(
        protected(set) string $name,
    ) {}
}
