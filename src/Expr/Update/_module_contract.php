<?php

/**
 * @moduleContract
 * @purpose Provides the SET clause value object for UPDATE statements.
 * @scope Single value object holding a column=value assignment.
 * @input Target column name and value expression.
 * @output SET clause data for UPDATE rendering.
 * @modulemap
 * SetClause => SET clause value object
 * @usecases
 * - [SetClause]: Developer → Define column assignment → UPDATE SET clause
 */
