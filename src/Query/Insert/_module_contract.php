<?php

/**
 * @moduleContract
 * @purpose Provide INSERT SQL query construction with WITH, INTO, and source (SELECT/VALUES) support.
 * @scope InsertQueryInterface and its implementation InsertQuery.
 * @input Target table, optional column names, optional source query.
 * @output SQL INSERT query DTO.
 * @modulemap
 * InsertQueryInterface => INSERT query contract
 * InsertQuery => INSERT query implementation (uses WithTrait)
 * @usecases
 * - [InsertQuery]: Developer → Insert rows → SQL INSERT statement
 */
