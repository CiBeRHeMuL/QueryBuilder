<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Provides SET clause value objects for UPDATE statements, including PostgreSQL ROW syntax extension.
 * @scope SetClause (single and multi-column) and PgSqlSetClause (with ROW support).
 * @input Target column name(s) and value expression(s).
 * @output SET clause data for UPDATE rendering.
 * @modulemap
 * SetClause => SET clause value object (single + multi-column)
 * PgSqlSetClause => PgSQL SET clause with ROW support
 * @usecases
 * - [SetClause]: Developer → Define column assignment → UPDATE SET clause
 * - [PgSqlSetClause]: Developer → Define multi-column ROW assignment → PgSQL UPDATE SET clause
 */
