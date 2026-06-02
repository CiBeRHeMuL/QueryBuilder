<?php

/**
 * @moduleContract
 * @purpose Provide enum types for INSERT query building.
 * @scope Sub-namespace for dialect-specific INSERT enums (PgSql).
 * @input Compile-time case selection.
 * @output Dialect-specific INSERT clause behavior.
 * @modulemap
 * PgSql/PgSqlOverrideValueMethodEnum => PostgreSQL OVERRIDING VALUE method
 * @usecases
 * - InsertQueryBuilder: User -> Build PgSql Insert -> OverrideValueMethod case
 */
