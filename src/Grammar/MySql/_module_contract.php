<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose MySQL SQL dialect grammar implementation with backtick identifier escaping, MySQL-specific query modifiers (LOW_PRIORITY, QUICK, IGNORE, HIGH_PRIORITY, STRAIGHT_JOIN, SQL_* hints), PARTITION clause, LIMIT offset,syntax, and FOR UPDATE/SHARE lock modes.
 * @scope MySQL SQL query building and grammar.
 * @input Query objects (SelectQueryInterface, DeleteQueryInterface, InsertQueryInterface, UpdateQueryInterface, ValuesQueryInterface)
 * @output BuiltQuery with MySQL-compatible SQL and parameters
 * @modulemap
 * MySqlGrammar => MySQL SQL grammar extending AbstractGrammar
 * @usecases
 * - MySqlGrammar: QueryBuilder → Build SELECT/DELETE queries → MySQL-compatible SQL
 */
