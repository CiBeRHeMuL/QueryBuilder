<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Provides GROUP BY grouping set expressions: CUBE, ROLLUP, and GROUPING SETS.
 * @scope Template method hierarchy for OLAP grouping expressions.
 * @input Column expressions and SQL prefix.
 * @output Rendered SQL fragments with parameters for CUBE/ROLLUP/GROUPING SETS.
 * @modulemap
 * AbstractGroupingSets => Abstract base for grouping set expressions
 * Cube => CUBE grouping expression
 * GroupingSets => GROUPING SETS expression
 * Rollup => ROLLUP grouping expression
 * @usecases
 * - [Cube]: Developer → Build CUBE grouping → SQL GROUP BY CUBE clause
 * - [Rollup]: Developer → Build ROLLUP grouping → SQL GROUP BY ROLLUP clause
 * - [GroupingSets]: Developer → Build GROUPING SETS → SQL GROUP BY GROUPING SETS clause
 */
