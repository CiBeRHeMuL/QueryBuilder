<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose PostgreSQL-specific DELETE query implementation with USING, JOIN, RETURNING clauses and MaybeReturnableQueryInterface support.
 * @scope PostgreSQL DELETE query building with advanced DML features.
 * @input Tables, conditions, using tables, joins, returning columns
 * @output PgSqlDeleteQuery instances with PostgreSQL-specific DELETE capabilities
 * @modulemap
 * PgSqlDeleteQuery => PostgreSQL DELETE query extending DeleteQuery with USING, JOIN, RETURNING
 * @usecases
 * - PgSqlDeleteQuery: QueryBuilder → Build DELETE ... USING ... RETURNING → PostgreSQL-compatible SQL
 */
