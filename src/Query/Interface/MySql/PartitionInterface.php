<?php

namespace AndrewGos\QueryBuilder\Query\Interface\MySql;

/**
 * This interface provides methods for working with PARTITION clause
 */
interface PartitionInterface
{
    /**
     * @var string[] $partitions
     */
    public array $partitions {
        get;
    }

    /**
     * @param string[] $partitions
     *
     * @return static
     */
    public function partition(array $partitions): static;

    /**
     * @param string[] $partitions
     *
     * @return static
     */
    public function addPartition(array $partitions): static;
}
