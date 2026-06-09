<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose PostgreSQL-specific MERGE query extensions: RETURNING, DO NOTHING.
 * @scope PgSql sub-namespace for MERGE query objects.
 * @input Target table, source, join condition, WHEN clauses, RETURNING columns.
 * @output PgSqlMergeQuery with RETURNING support.
 * @modulemap
 * PgSqlMergeQuery => PostgreSQL MERGE query with RETURNING
 * @usecases
 * - [PgSqlMergeQuery]: Developer → Build PgSQL MERGE with CTE + RETURNING → PgSqlGrammar renders
 */
