<?php

namespace AndrewGos\QueryBuilder\Query\Trait\MySql;

// region MODULE_CONTRACT [DOMAIN(8): Trait; CONCEPT(8): PartitionTrait; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose Provides shared implementation of MySQL PARTITION clause functionality for queries.
 * @scope MySQL partition selection trait.
 * @input string[] $partitions
 * @output PartitionTrait methods for partition management
 * @invariants
 * - $partitions stores partition names as string array
 * @modulemap
 * PartitionTrait => MySQL PARTITION clause implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MySQL, PARTITION, trait, dialect

// region TRAIT_PartitionTrait [DOMAIN(8): Trait; CONCEPT(8): PartitionTrait; TECH(8): Dialect]
/**
 * @purpose Provides shared implementation of PartitionInterface for MySQL partition clause support.
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
    // region METHOD_partition [DOMAIN(8): Trait; TECH(8): Partition]
    /**
     * @purpose Set partitions for MySQL PARTITION clause.
     */
    public function partition(array $partitions): static
    {
        $this->partitions = $partitions;

        return $this;
    }
    // endregion METHOD_partition

    /**
     * @param string[] $partitions
     *
     * @return static
     */
    // region METHOD_addPartition [DOMAIN(8): Trait; TECH(8): Partition]
    /**
     * @purpose Add additional partitions for MySQL PARTITION clause.
     */
    public function addPartition(array $partitions): static
    {
        $this->partitions = array_merge($this->partitions, $partitions);

        return $this;
    }
    // endregion METHOD_addPartition
}
// endregion TRAIT_PartitionTrait
