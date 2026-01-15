<?php

namespace AndrewGos\QueryBuilder\Expr\Lock;

use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

interface LockModeInterface
{
    public function getSql(GrammarInterface $grammar): string;
}
