<?php

/**
 * @moduleContract
 * @purpose Provide enum types for SQL lock mode clause building.
 * @scope Standard lock mode and sub-namespaces for MySQL and PostgreSQL dialect-specific lock modes.
 * @input Compile-time case selection; GrammarInterface for dialect formatting.
 * @output SQL lock mode clause fragment strings.
 * @modulemap
 * StandardLockModeEnum => Standard lock mode (FOR UPDATE)
 * MySql/MySqlLockModeEnum => MySQL lock modes
 * MySql/MySqlLockWaitModeEnum => MySQL lock wait modes
 * PgSql/PgSqlLockModeEnum => PostgreSQL lock modes
 * PgSql/PgSqlLockWaitModeEnum => PostgreSQL lock wait modes
 * @usecases
 * - SelectQueryBuilder: User -> Build Lock Clause -> LockMode case
 * - SelectQueryBuilder: User -> Build Lock Wait Clause -> LockWaitMode case
 */
