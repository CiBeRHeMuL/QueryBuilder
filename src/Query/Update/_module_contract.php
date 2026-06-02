<?php

/**
 * @moduleContract
 * @purpose Provide UPDATE SQL query contract with WITH, FROM, and WHERE support.
 * @scope UpdateQueryInterface only (no implementation in this namespace).
 * @input Table, SET values, optional WHERE conditions.
 * @output SQL UPDATE query contract.
 * @modulemap
 * UpdateQueryInterface => UPDATE query contract (extends WithInterface, FromInterface, WhereInterface)
 * @usecases
 * - [UpdateQueryInterface]: Developer → Update rows → SQL UPDATE statement
 */
