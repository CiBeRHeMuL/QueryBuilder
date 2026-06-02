<?php

/**
 * @moduleContract
 * @purpose Root namespace for the QueryBuilder library — provides a fluent SQL query building API.
 * @scope Query interfaces, expressions, grammar, helpers, exceptions, builders.
 * @input High-level query definitions (Select, Insert, Update, Delete, Values, Merge)
 * @output BuiltQuery (SQL string + bound parameters)
 * @modulemap
 * Grammar [10][SQL dialect grammar base and interface] => Grammar/
 * Builder [8][Value-to-expression dispatcher] => Builder/
 * Expr [9][SQL expression nodes] => Expr/
 * Helper [9][Static expression utilities] => Helper/
 * Exception [9][Domain-specific exception factory] => Exception/
 * Query [10][Query interfaces and implementation] => Query/
 * @usecases
 * - Grammar: Client → Build query → BuiltQuery
 * - Expr: Grammar → Render expression → SQL fragment
 * - Helper: Grammar/Builder → Validate/merge expressions → Normalized data
 */
