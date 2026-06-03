<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Provides PostgreSQL-specific UPDATE query with FROM, JOIN, and RETURNING support.
 * @scope PgSqlUpdateQuery extending UpdateQuery.
 * @input Target table, SET values, optional FROM, JOIN, WHERE, RETURNING.
 * @output PgSQL UPDATE query DTO.
 * @modulemap
 * PgSqlUpdateQuery => PgSQL UPDATE query with FROM, JOIN, RETURNING support
 * @usecases
 * - [PgSqlUpdateQuery]: Developer → Update rows with PgSQL extensions → PgSQL UPDATE
 */
