<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose MySQL-specific SELECT query implementation with HIGH_PRIORITY, STRAIGHT_JOIN, SQL_* hints, PARTITION clause, and FOR UPDATE/FOR SHARE lock modes.
 * @scope MySQL SELECT query building.
 * @input Columns, tables, conditions, partitions, SQL hints, lock modes
 * @output MySqlSelectQuery instances with MySQL-specific SELECT capabilities
 * @modulemap
 * MySqlSelectQuery => MySQL SELECT query extending SelectQuery with MySQL hints
 * @usecases
 * - MySqlSelectQuery: QueryBuilder → Build SELECT with SQL_CALC_FOUND_ROWS PARTITION → MySQL-compatible SQL
 */
