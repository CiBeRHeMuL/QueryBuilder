<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose PostgreSQL-specific table expressions with ONLY modifier for table inheritance hierarchies.
 * @scope PostgreSQL SELECT table references.
 * @input string $name, bool $only
 * @output PgSqlSelectTable instances extending SelectTable
 * @modulemap
 * PgSqlSelectTable => PostgreSQL table reference with ONLY modifier for inheritance
 * @usecases
 * - PgSqlSelectTable: QueryBuilder → Reference table with ONLY → Exclude child tables in PostgreSQL
 */
