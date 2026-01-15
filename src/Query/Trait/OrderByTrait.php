<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Helper\HExpr;

/**
 * This trait provides functionality of OrderByInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\OrderByInterface
 */
trait OrderByTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $orderBy = [];

    /**
     * @inheritDoc
     */
    public function orderBy(array $columns): static
    {
        $this->orderBy = HExpr::normalizeOrderBy($columns);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addOrderBy(array $columns): static
    {
        $this->orderBy = array_merge(
            $this->orderBy,
            HExpr::normalizeOrderBy($columns),
        );

        return $this;
    }
}
