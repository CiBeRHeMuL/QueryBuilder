<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose MySQL-specific query traits including PartitionTrait for PARTITION clause implementation shared across query types.
 * @scope MySQL trait definitions.
 * @input Partition names as string arrays
 * @output PartitionTrait for MySQL partition clause support in queries
 * @modulemap
 * PartitionTrait => MySQL PARTITION clause implementation trait
 * @usecases
 * - PartitionTrait: Query → Use partition selection → MySQL PARTITION clause support
 */
