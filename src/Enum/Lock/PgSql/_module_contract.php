<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Provide PostgreSQL-specific lock mode enum types.
 * @scope PostgreSQL lock modes (FOR UPDATE, FOR NO KEY UPDATE, FOR SHARE, FOR KEY SHARE) and lock wait modes (NOWAIT, SKIP LOCKED).
 * @input Compile-time case selection; GrammarInterface for dialect formatting.
 * @output SQL lock mode clause fragment strings for PostgreSQL dialect.
 * @modulemap
 * PgSqlLockModeEnum => PostgreSQL lock modes (FOR UPDATE, FOR NO KEY UPDATE, FOR SHARE, FOR KEY SHARE)
 * PgSqlLockWaitModeEnum => PostgreSQL lock wait modes (NOWAIT, SKIP LOCKED)
 * @usecases
 * - SelectQueryBuilder: User -> Build PgSql Lock Clause -> PgSqlLockModeEnum case
 * - SelectQueryBuilder: User -> Build PgSql Lock Wait Clause -> PgSqlLockWaitModeEnum case
 */
