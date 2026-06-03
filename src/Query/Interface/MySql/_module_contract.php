<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose MySQL-specific query interfaces including PARTITION clause support for SELECT and DELETE queries.
 * @scope MySQL query interface definitions.
 * @input Partition names as string arrays
 * @output Interfaces for MySQL-partition-aware query implementations
 * @modulemap
 * PartitionInterface => MySQL PARTITION clause interface
 * @usecases
 * - PartitionInterface: QueryBuilder → Implement MySQL partition selection → Partition-pruned queries
 */
