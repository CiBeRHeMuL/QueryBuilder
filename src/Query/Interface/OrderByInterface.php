<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Order\OrderColumn;

/**
 * This interface provides methods for working with ORDER BY clause
 *
 * @template TOrderBy of array<string, int|string>|array<int, string|ExprInterface|OrderColumn> column => order, expression or OrderColumn
 */
interface OrderByInterface
{
    /**
     * @var OrderColumn[]
     */
    public array $orderBy {
        get;
    }

    /**
     * @param TOrderBy $columns
     *
     * @return static
     */
    public function orderBy(array $columns): static;

    /**
     * @param TOrderBy $columns
     *
     * @return static
     */
    public function addOrderBy(array $columns): static;
}
