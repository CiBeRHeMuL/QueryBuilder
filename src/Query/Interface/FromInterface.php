<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

/**
 * This interface provides methods for working with FROM clause
 *
 * @template TTable of string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable
 * @template TNormalizedTable of ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable
 */
interface FromInterface
{
    /**
     * @var array<int|string, TNormalizedTable>
     */
    public array $from {
        get;
    }

    /**
     * @param array<int|string, TTable> $tables
     *
     * @return static
     */
    public function from(array $tables): static;

    /**
     * @param array<int|string, TTable> $tables
     *
     * @return static
     */
    public function addFrom(array $tables): static;
}
