<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

/**
 * This trait provides functionality of WithInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\WhereInterface
 */
trait WithTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $with = [];
    protected(set) bool $withRecursive = false;

    /**
     * @inheritDoc
     */
    public function with(array $with, bool $recursive = false): static
    {
        $this->withRecursive = $recursive;
        $this->with = $with;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addWith(array $with, bool $recursive = false): static
    {
        $this->withRecursive = $recursive;
        $this->with = array_merge($this->with, $with);

        return $this;
    }
}
