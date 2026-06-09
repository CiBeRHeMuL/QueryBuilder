<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Provides the JOIN clause value object for defining table joins in SQL queries.
 * @scope Single value object covering all JOIN types (INNER, LEFT, RIGHT, CROSS, FULL, NATURAL).
 * @input Join type, table reference, conditions, natural join flag.
 * @output Join clause data for SQL rendering.
 * @modulemap
 * JoinTable => Join clause value object
 * @usecases
 * - [JoinTable]: Developer → Define table join → JOIN clause in SQL query
 */
