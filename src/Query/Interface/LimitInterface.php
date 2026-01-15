<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Enum\LimitBoundTypeEnum;
use AndrewGos\QueryBuilder\Expr\ExprInterface;

/**
 * This interface provides methods for working with OFFSET, LIMIT, FETCH clause
 */
interface LimitInterface
{
    public int|ExprInterface $offset {
        get;
    }
    public int|ExprInterface|null $limit {
        get;
    }
    public LimitBoundTypeEnum $limitBoundType {
        get;
    }

    /**
     * @param int|ExprInterface $offset
     *
     * @return static
     */
    public function offset(int|ExprInterface $offset): static;

    /**
     * @param int|ExprInterface|null $limit
     * @param LimitBoundTypeEnum $boundType
     *
     * @return static
     */
    public function limit(int|ExprInterface|null $limit, LimitBoundTypeEnum $boundType = LimitBoundTypeEnum::Only): static;
}
