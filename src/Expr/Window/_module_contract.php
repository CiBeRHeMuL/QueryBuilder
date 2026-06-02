<?php

/**
 * @moduleContract
 * @purpose Provides window function clause builders: OVER clause and Window definition (PARTITION BY, ORDER BY, frame).
 * @scope Full window specification including named window references, partitioning, ordering, and frame types (RANGE/ROWS/GROUPS).
 * @input Function expressions, partition columns, order definitions, frame parameters.
 * @output Rendered OVER clause and window definition SQL with bound parameters.
 * @modulemap
 * Over => OVER clause for window functions
 * Window => Fluent window definition builder
 * @usecases
 * - [Over]: Developer → Wrap function with OVER clause → Window function expression
 * - [Window]: Developer → Build window definition → PARTITION BY / ORDER BY / frame spec
 */
