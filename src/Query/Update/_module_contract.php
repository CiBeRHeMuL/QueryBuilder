<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Provide UPDATE SQL query contract and implementations for all dialects.
 * @scope UpdateQueryInterface, base UpdateQuery, MySqlUpdateQuery, PgSqlUpdateQuery.
 * @input Table, SET values, optional WHERE conditions.
 * @output SQL UPDATE query DTOs for all supported dialects.
 * @modulemap
 * UpdateQueryInterface => UPDATE query contract (extends WithInterface, WhereInterface)
 * UpdateQuery => ANSI-standard UPDATE query implementation
 * MySqlUpdateQuery => MySQL UPDATE query with multi-table, PARTITION, ORDER BY, LIMIT support
 * PgSqlUpdateQuery => PgSQL UPDATE query with FROM, JOIN, RETURNING support
 * @usecases
 * - [UpdateQuery]: Developer → Update rows → ANSI SQL UPDATE statement
 * - [MySqlUpdateQuery]: Developer → Update rows with MySQL extensions → MySQL UPDATE
 * - [PgSqlUpdateQuery]: Developer → Update rows with PgSQL extensions → PgSQL UPDATE
 */
