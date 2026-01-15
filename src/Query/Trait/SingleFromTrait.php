<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Helper\HExpr;

/**
 * This trait provides functionality of FromInterface
 * for queries which allow using only single table.
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\FromInterface
 */
trait SingleFromTrait
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
        if ($tables) {
            $this->from = array_map(
                HExpr::normalizeTable(...),
                array_slice($tables, 0, 1),
            );
        } else {
            $this->from = [];
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addFrom(array $tables): static
    {
        return $this->from($tables);
    }
}
