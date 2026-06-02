<?php

/**
 * @moduleContract
 * @purpose MySQL-specific lock mode expressions for SELECT ... FOR UPDATE / FOR SHARE with table lists and NOWAIT/SKIP LOCKED options.
 * @scope MySQL locking syntax generation.
 * @input MySqlLockModeEnum $mode, string[] $tables, MySqlLockWaitModeEnum $waitMode
 * @output MySqlLockMode instances implementing LockModeInterface
 * @modulemap
 * MySqlLockMode => MySQL lock mode expression with table targeting and wait mode
 * @usecases
 * - MySqlLockMode: QueryBuilder → Generate FOR UPDATE OF table NOWAIT → MySQL-compatible SQL
 */
