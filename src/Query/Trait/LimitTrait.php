<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Enum\LimitBoundTypeEnum;
use AndrewGos\QueryBuilder\Expr\ExprInterface;

/**
 * This trait provides functionality of LimitInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\LimitInterface
 */
trait LimitTrait
{
    protected(set) int|ExprInterface $offset = 0;
    protected(set) int|ExprInterface|null $limit = null;
    protected(set) LimitBoundTypeEnum $limitBoundType = LimitBoundTypeEnum::Only;

    /**
     * @inheritDoc
     */
    public function offset(int|ExprInterface $offset): static
    {
        $this->offset = is_int($offset) ? max(0, $offset) : $offset;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function limit(int|ExprInterface|null $limit, LimitBoundTypeEnum $boundType = LimitBoundTypeEnum::Only): static
    {
        $this->limit = is_int($limit) ? max(0, $limit) : $limit;
        $this->limitBoundType = $boundType;

        return $this;
    }
}
