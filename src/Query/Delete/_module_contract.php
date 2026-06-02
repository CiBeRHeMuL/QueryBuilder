<?php

/**
 * @moduleContract
 * @purpose Provide DELETE SQL query construction with WITH, FROM, and WHERE support.
 * @scope DeleteQueryInterface and its implementation DeleteQuery.
 * @input Table name with optional WHERE conditions.
 * @output SQL DELETE query DTO.
 * @modulemap
 * DeleteQueryInterface => DELETE query contract
 * DeleteQuery => DELETE query implementation (uses WithTrait, SingleFromTrait, WhereTrait)
 * @usecases
 * - [DeleteQuery]: Developer → Delete rows → SQL DELETE statement
 */
