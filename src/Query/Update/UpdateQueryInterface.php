<?php

namespace AndrewGos\QueryBuilder\Query\Update;

use AndrewGos\QueryBuilder\Query\Interface\FromInterface;
use AndrewGos\QueryBuilder\Query\Interface\WhereInterface;
use AndrewGos\QueryBuilder\Query\Interface\WithInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Update; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for UPDATE SQL queries with WITH, FROM, and WHERE support.
 * @scope Interface extending clause interfaces for UPDATE operations.
 * @input Table, SET values, and conditions via parent interfaces.
 * @output Contract for UPDATE query DTO.
 * @modulemap
 * INTERFACE UpdateQueryInterface => UPDATE query contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: UPDATE, SQL, interface, query contract, update, WithInterface, FromInterface, WhereInterface

// region INTERFACE_UpdateQueryInterface [DOMAIN(8): Query; CONCEPT(9): Update; TECH(8): SQL]
/**
 * @purpose Contract composing WITH, FROM, and WHERE interfaces for UPDATE queries.
 */
interface UpdateQueryInterface extends WithInterface, FromInterface, WhereInterface {}
// endregion INTERFACE_UpdateQueryInterface
