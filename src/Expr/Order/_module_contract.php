<?php

/**
 * @moduleContract
 * @purpose Provides the ORDER BY column value object for sort specification.
 * @scope Single value object holding column expression and sort direction.
 * @input Expression and sort order string (ASC, DESC, or domain-specific).
 * @output Order column data for ORDER BY rendering.
 * @modulemap
 * OrderColumn => Sort column value object
 * @usecases
 * - [OrderColumn]: Developer → Define sort column → ORDER BY clause
 */
