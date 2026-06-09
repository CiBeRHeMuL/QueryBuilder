<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Builder namespace — provides value-to-expression dispatch for the query builder.
 * @scope Type-based value normalization, sub-query wrapping, array and enum handling.
 * @input Mixed values of various types (scalars, enums, expression interfaces, queries, arrays)
 * @output ExprInterface instances
 * @modulemap
 * ValueBuilder [10][Value-to-expression type dispatcher] => ValueBuilder.php
 * @usecases
 * - ValueBuilder: Grammar/Helper → Normalize value → ExprInterface
 */
