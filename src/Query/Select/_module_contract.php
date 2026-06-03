<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Provide SELECT SQL query construction with full clause support (WITH, FROM, JOIN, WHERE, GROUP BY, HAVING, WINDOW, ORDER BY, LIMIT, LOCK, UNION/INTERSECT/EXCEPT).
 * @scope SelectQueryInterface and its implementation SelectQuery.
 * @input Columns, tables, conditions, ordering, limits, set operations.
 * @output SQL SELECT query DTO.
 * @modulemap
 * SelectQueryInterface => SELECT query contract (extends 7 clause interfaces)
 * SelectQuery => SELECT query implementation (uses 7 traits: WithTrait, FromTrait, WhereTrait, JoinTrait, OperationsTrait, OrderByTrait, LimitTrait)
 * @usecases
 * - [SelectQuery]: Developer → Build SELECT → SQL SELECT statement
 */
