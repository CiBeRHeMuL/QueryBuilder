<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose PostgreSQL-specific lock mode expressions for SELECT ... FOR UPDATE/FOR NO KEY UPDATE/FOR SHARE/FOR KEY SHARE with table lists and wait modes.
 * @scope PostgreSQL locking syntax generation.
 * @input PgSqlLockModeEnum $mode, string[] $tables, PgSqlLockWaitModeEnum $waitMode
 * @output PgSqlLockMode instances implementing LockModeInterface
 * @modulemap
 * PgSqlLockMode => PostgreSQL lock mode expression with various lock strengths
 * @usecases
 * - PgSqlLockMode: QueryBuilder → Generate FOR NO KEY UPDATE OF table NOWAIT → PostgreSQL-compatible SQL
 */
