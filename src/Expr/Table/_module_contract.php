<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Provides the table reference value object for SELECT, FROM, and JOIN clauses.
 * @scope Simple value object holding a table name reference.
 * @input Table name string.
 * @output Table reference data for SQL rendering.
 * @modulemap
 * SelectTable => Table reference value object
 * @usecases
 * - [SelectTable]: Developer → Reference a table → FROM/JOIN clause
 */
