<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Provide PostgreSQL-specific INSERT enum types.
 * @scope OVERRIDING SYSTEM/USER VALUE method enum for PostgreSQL INSERT.
 * @input Compile-time case selection.
 * @output PostgreSQL OVERRIDING clause behavior.
 * @modulemap
 * PgSqlOverrideValueMethodEnum => PostgreSQL OVERRIDING VALUE method (SYSTEM, USER)
 * @usecases
 * - InsertQueryBuilder: User -> Build PgSql Insert -> OverrideValueMethod case
 */
