<?php

/**
 * @moduleContract
 * @purpose Provides MySQL-specific UPDATE query with multi-table support, PARTITION, ORDER BY, LIMIT, and LOW_PRIORITY/IGNORE flags.
 * @scope MySqlUpdateQuery extending UpdateQuery.
 * @input Table(s), SET values, optional PARTITION, ORDER BY, LIMIT, LOW_PRIORITY, IGNORE.
 * @output MySQL UPDATE query DTO.
 * @modulemap
 * MySqlUpdateQuery => MySQL UPDATE query with multi-table support
 * @usecases
 * - [MySqlUpdateQuery]: Developer → Update rows with MySQL extensions → MySQL UPDATE
 */
