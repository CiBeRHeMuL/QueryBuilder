<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Provide MySQL-specific lock mode enum types.
 * @scope MySQL lock modes (FOR UPDATE / FOR SHARE) and lock wait modes (NOWAIT / SKIP LOCKED).
 * @input Compile-time case selection; GrammarInterface for dialect formatting.
 * @output SQL lock mode clause fragment strings for MySQL dialect.
 * @modulemap
 * MySqlLockModeEnum => MySQL lock modes (FOR UPDATE, FOR SHARE)
 * MySqlLockWaitModeEnum => MySQL lock wait modes (NOWAIT, SKIP LOCKED)
 * @usecases
 * - SelectQueryBuilder: User -> Build MySQL Lock Clause -> MySqlLockModeEnum case
 * - SelectQueryBuilder: User -> Build MySQL Lock Wait Clause -> MySqlLockWaitModeEnum case
 */
