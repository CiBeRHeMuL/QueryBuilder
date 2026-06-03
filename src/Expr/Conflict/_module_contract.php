<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Provide interfaces for ON CONFLICT clause building — conflict action and conflict target (polymorphic, like LockModeInterface).
 * @scope ConflictActionInterface and ConflictTargetInterface contracts.
 * @input GrammarInterface for SQL generation.
 * @output Interfaces for conflict action/target SQL string + params generation.
 * @modulemap
 * ConflictActionInterface => Contract for conflict action SQL generation (DO NOTHING / DO UPDATE SET ...)
 * ConflictTargetInterface => Contract for conflict target SQL generation (columns / ON CONSTRAINT)
 * @usecases
 * - [ConflictActionInterface]: Grammar → getSql() + getParams() → ON CONFLICT action fragment
 * - [ConflictTargetInterface]: Grammar → getSql() + getParams() → ON CONFLICT target fragment
 */
