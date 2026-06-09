<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose PostgreSQL conflict target and action implementations.
 * @scope ConflictTargetColumns, ConflictTargetConstraint, ConflictActionDoNothing, ConflictActionDoUpdate.
 * @input Column definitions, constraint name, SET assignments, WHERE conditions.
 * @output SQL strings and params for PostgreSQL ON CONFLICT clause components.
 * @modulemap
 * PgSqlConflictTargetColumns => ConflictTargetInterface impl — (col1 COLLATE opclass, ...) [WHERE pred]
 * PgSqlConflictTargetConstraint => ConflictTargetInterface impl — ON CONSTRAINT name
 * PgSqlConflictActionDoNothing => ConflictActionInterface impl — DO NOTHING
 * PgSqlConflictActionDoUpdate => ConflictActionInterface impl — DO UPDATE SET col = val, ... WHERE cond
 * @usecases
 * - [PgSqlConflictTargetColumns]: QueryBuilder → Build ON CONFLICT (columns) → PostgreSQL conflict target
 * - [PgSqlConflictTargetConstraint]: QueryBuilder → Build ON CONFLICT ON CONSTRAINT → PostgreSQL conflict target
 * - [PgSqlConflictActionDoNothing]: QueryBuilder → Build DO NOTHING → PostgreSQL conflict action
 * - [PgSqlConflictActionDoUpdate]: QueryBuilder → Build DO UPDATE SET ... WHERE ... → PostgreSQL conflict action
 */
