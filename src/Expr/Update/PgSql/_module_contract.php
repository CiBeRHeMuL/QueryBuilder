<?php

/**
 * @moduleContract
 * @purpose Provides PostgreSQL-specific SET clause extension with ROW() syntax support.
 * @scope PgSqlSetClause with isRow flag for ROW(expr1, expr2, ...) multi-column syntax.
 * @input Target column(s), value, and ROW flag.
 * @output PostgreSQL SET clause value object.
 * @modulemap
 * PgSqlSetClause => PgSQL SET clause with ROW support
 * @usecases
 * - [PgSqlSetClause]: Developer → Multi-column ROW assignment → PgSQL UPDATE SET ROW clause
 */
