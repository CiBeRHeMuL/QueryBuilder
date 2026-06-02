<?php

namespace AndrewGos\QueryBuilder\Query\Interface\MySql;

// region MODULE_CONTRACT [DOMAIN(8): Interface; CONCEPT(8): PartitionInterface; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose Defines the contract for MySQL PARTITION clause support in queries.
 * @scope MySQL partition selection for queries.
 * @input string[] $partitions
 * @output Interface for partition-aware queries
 * @invariants
 * - $partitions array contains partition names as strings
 * @modulemap
 * PartitionInterface => MySQL PARTITION clause interface
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MySQL, PARTITION, interface, dialect

// region INTERFACE_PartitionInterface [DOMAIN(8): Interface; CONCEPT(8): PartitionInterface; TECH(8): Dialect]
/**
 * @purpose Contract for queries supporting MySQL PARTITION clause with partition get, set, and add operations.
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
// endregion INTERFACE_PartitionInterface
