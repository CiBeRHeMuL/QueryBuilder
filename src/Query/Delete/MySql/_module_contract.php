<?php

/**
 * @moduleContract
 * @purpose MySQL-specific DELETE query implementation with LOW_PRIORITY, QUICK, IGNORE modifiers, PARTITION clause, ORDER BY, and LIMIT support.
 * @scope MySQL DELETE query building.
 * @input Tables, conditions, partitions, order, limit
 * @output MySqlDeleteQuery instances with MySQL-specific DELETE capabilities
 * @modulemap
 * MySqlDeleteQuery => MySQL DELETE query extending DeleteQuery with MySQL modifiers
 * @usecases
 * - MySqlDeleteQuery: QueryBuilder → Build DELETE with LOW_PRIORITY QUICK IGNORE PARTITION → MySQL-compatible SQL
 */
