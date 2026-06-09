<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Provides value objects and builders for Common Table Expression (CTE) clauses, including WITH, SEARCH, and CYCLE.
 * @scope Fluent CTE definition with optional recursive traversal control (SEARCH) and cycle detection (CYCLE).
 * @input Query definitions, column lists, traversal parameters.
 * @output Configured CTE clause objects for SQL rendering.
 * @modulemap
 * Cycle => Cycle detection value object for recursive CTEs
 * Search => Search clause (BFS/DFS) value object for recursive CTEs
 * WithQuery => Fluent CTE builder with search/cycle support
 * @usecases
 * - [WithQuery]: Developer → Build recursive CTE → SQL WITH clause
 * - [Search]: Recursive CTE → Configure BFS/DFS → SEARCH clause
 * - [Cycle]: Recursive CTE → Configure cycle detection → CYCLE clause
 */
