<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Helper\HExpr;

/**
 * This trait provides functionality of FromInterface
 * for queries which allow using not only single table.
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\FromInterface
 */
trait FromTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $from = [];

    /**
     * @inheritDoc
     */
    public function from(array $tables): static
    {
        $this->from = array_map(HExpr::normalizeTable(...), $tables);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addFrom(array $tables): static
    {
        $this->from = array_merge(
            $this->from,
            array_map(HExpr::normalizeTable(...), $tables),
        );
        return $this;
    }
}
