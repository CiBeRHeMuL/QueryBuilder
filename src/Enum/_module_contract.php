<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Provide PHP enum types that represent SQL keyword constants across all supported dialects (standard SQL, MySQL, PostgreSQL).
 * @scope Top-level enums for JOIN types, LIMIT bound types, and set operations.
 * @input Compile-time case selection by the query builder.
 * @output SQL clause fragment strings via getSql() methods.
 * @modulemap
 * JoinTypeEnum => SQL JOIN types (INNER, LEFT, RIGHT, FULL, CROSS)
 * LimitBoundTypeEnum => LIMIT bound types (ONLY, WITH TIES)
 * SetOperationEnum => Set operations (UNION, INTERSECT, EXCEPT with ALL/DISTINCT)
 * @usecases
 * - QueryBuilder: User -> Build Join Clause -> JoinTypeEnum case
 * - QueryBuilder: User -> Build Limit Clause -> LimitBoundTypeEnum case
 * - QueryBuilder: User -> Build Set Operation -> SetOperationEnum case
 */
