<?php

/**
 * @moduleContract
 * @purpose PostgreSQL-specific INSERT query implementation with OVERRIDING USER/SYSTEM VALUE method and RETURNING clause support.
 * @scope PostgreSQL INSERT query building.
 * @input Table, columns, values, override method, returning columns
 * @output PgSqlInsertQuery instances with PostgreSQL-specific INSERT capabilities
 * @modulemap
 * PgSqlInsertQuery => PostgreSQL INSERT query extending InsertQuery with OVERRIDING and RETURNING
 * @usecases
 * - PgSqlInsertQuery: QueryBuilder → Build INSERT ... OVERRIDING SYSTEM VALUE RETURNING → PostgreSQL-compatible SQL
 */
