<?php

namespace AndrewGos\QueryBuilder\Expr\Order;

use AndrewGos\QueryBuilder\Expr\ExprInterface;

final readonly class OrderColumn
{
    /**
     * @param ExprInterface|int|string $expr
     * @param string $order sort order. Can be simple ASC or DESC or you can use domain specific order like USING for Postgres and NULLS FIRST/LAST
     */
    public function __construct(
        private(set) ExprInterface|int|string $expr,
        private(set) string $order = 'ASC',
    ) {}
}
