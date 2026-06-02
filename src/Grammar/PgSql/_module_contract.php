<?php

/**
 * @moduleContract
 * @purpose PostgreSQL SQL dialect grammar implementation with double-quote identifier escaping, DISTINCT ON, ONLY table modifier, CTE MATERIALIZED/NOT MATERIALIZED, RETURNING clause, USING clause, and FOR UPDATE/SHARE lock modes.
 * @scope PostgreSQL SQL query building and grammar.
 * @input Query objects (SelectQueryInterface, DeleteQueryInterface, MaybeReturnableQueryInterface, etc.)
 * @output BuiltQuery with PostgreSQL-compatible SQL and parameters
 * @modulemap
 * PgSqlGrammar => PostgreSQL SQL grammar extending AbstractGrammar
 * @usecases
 * - PgSqlGrammar: QueryBuilder → Build SELECT/DELETE/VALUES queries → PostgreSQL-compatible SQL
 */
