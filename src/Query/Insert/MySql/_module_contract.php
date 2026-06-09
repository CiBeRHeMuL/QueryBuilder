<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose MySQL-specific INSERT query implementation with LOW_PRIORITY, DELAYED, HIGH_PRIORITY, IGNORE modifiers and PARTITION support.
 * @scope MySQL INSERT query building.
 * @input Table, columns, values, modifiers, partitions
 * @output MySqlInsertQuery instances with MySQL-specific INSERT capabilities
 * @modulemap
 * MySqlInsertQuery => MySQL INSERT query extending InsertQuery with modifiers and PartitionInterface
 * @usecases
 * - MySqlInsertQuery: QueryBuilder → Build INSERT LOW_PRIORITY INTO ... → MySQL-compatible SQL
 */
