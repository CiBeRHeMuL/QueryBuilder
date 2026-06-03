<?php

/**
 * @moduleContract
 * @purpose Universal ANSI SQL grammar implementation with double-quote identifier escaping. Lightweight default grammar with no dialect-specific extensions.
 * @scope SQL query building with standard ANSI SQL identifier quoting.
 * @input Query objects (SelectQueryInterface, ValuesQueryInterface, DeleteQueryInterface, InsertQueryInterface, UpdateQueryInterface)
 * @output BuiltQuery with ANSI SQL-compatible SQL and parameters
 * @modulemap
 * DefaultGrammar => ANSI SQL default grammar extending AbstractGrammar
 * @usecases
 * - DefaultGrammar: QueryBuilder → Build any query → ANSI SQL-compatible BuiltQuery
 */
