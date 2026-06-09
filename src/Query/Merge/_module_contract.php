<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Define MERGE query construction (ANSI SQL:2008) and PostgreSQL-specific extensions (RETURNING, DO NOTHING, WHEN NOT MATCHED BY SOURCE).
 * @scope Sub-namespace partitioning: MergeInterface, MergeQuery, PgSql sub-namespace.
 * @input Target table, source table/query, join condition, WHEN clauses.
 * @output Immutable MERGE query DTOs ready for SQL rendering.
 * @modulemap
 * MergeQueryInterface => ANSI MERGE query contract
 * MergeQuery => ANSI MERGE query implementation
 * PgSql/ => PostgreSQL-specific MERGE query (with BY SOURCE + RETURNING)
 * @usecases
 * - [MergeQuery]: Developer → Build ANSI MERGE → Grammar renders
 * - [PgSqlMergeQuery]: Developer → Build PgSQL MERGE (BY SOURCE + RETURNING) → PgSqlGrammar renders
 */
