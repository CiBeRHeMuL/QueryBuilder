<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Provide enum types for CTE (Common Table Expression) query building.
 * @scope CTE search type (BREADTH / DEPTH) for recursive CTEs.
 * @input Compile-time case selection by the CTE query builder.
 * @output SQL SEARCH clause fragment strings via getSql() methods.
 * @modulemap
 * SearchTypeEnum => CTE search types (BREADTH, DEPTH)
 * @usecases
 * - CteBuilder: User -> Build Recursive CTE -> SearchTypeEnum case
 */
