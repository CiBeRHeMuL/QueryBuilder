<?php

namespace AndrewGos\QueryBuilder\Query\Trait\MySql;

/**
 * This trait provides functionality of MySql\PartitionInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\MySql\PartitionInterface
 */
trait PartitionTrait
{
    /**
     * @var string[] $partitions
     */
    protected(set) array $partitions = [];

    /**
     * @param string[] $partitions
     *
     * @return static
     */
    public function partition(array $partitions): static
    {
        $this->partitions = $partitions;

        return $this;
    }

    /**
     * @param string[] $partitions
     *
     * @return static
     */
    public function addPartition(array $partitions): static
    {
        $this->partitions = array_merge($this->partitions, $partitions);

        return $this;
    }
}
