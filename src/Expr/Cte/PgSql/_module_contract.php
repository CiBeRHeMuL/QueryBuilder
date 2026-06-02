<?php

/**
 * @moduleContract
 * @purpose PostgreSQL-specific CTE (Common Table Expression) extensions including materialization hints and SEARCH/CYCLE clause support.
 * @scope PostgreSQL WITH query dialect extensions.
 * @input Query objects implementing MaybeReturnableQueryInterface
 * @output PgSqlWithQuery instances with PostgreSQL-specific CTE features
 * @modulemap
 * PgSqlWithQuery => PostgreSQL WITH query with MATERIALIZED/NOT MATERIALIZED support
 * @usecases
 * - PgSqlWithQuery: QueryBuilder → Build CTE with materialization hint → PostgreSQL-compatible SQL
 */
