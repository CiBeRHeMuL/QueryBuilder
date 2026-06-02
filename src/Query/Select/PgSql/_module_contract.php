<?php

/**
 * @moduleContract
 * @purpose PostgreSQL-specific SELECT query implementation with DISTINCT ON clause and FOR UPDATE/FOR SHARE/FOR NO KEY UPDATE/FOR KEY SHARE lock modes.
 * @scope PostgreSQL SELECT query building.
 * @input Columns, tables, conditions, distinct on columns, lock modes
 * @output PgSqlSelectQuery instances with PostgreSQL-specific SELECT capabilities
 * @modulemap
 * PgSqlSelectQuery => PostgreSQL SELECT query extending SelectQuery with DISTINCT ON and lock modes
 * @usecases
 * - PgSqlSelectQuery: QueryBuilder → Build SELECT DISTINCT ON ... FOR UPDATE → PostgreSQL-compatible SQL
 */
