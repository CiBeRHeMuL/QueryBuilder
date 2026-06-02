<?php

namespace AndrewGos\QueryBuilder\Query\Delete;

use AndrewGos\QueryBuilder\Query\Interface\FromInterface;
use AndrewGos\QueryBuilder\Query\Interface\WhereInterface;
use AndrewGos\QueryBuilder\Query\Interface\WithInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Delete; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for DELETE SQL queries with WITH, FROM, and WHERE support.
 * @scope Interface extending clause interfaces for DELETE operations.
 * @input Table and conditions via parent interfaces.
 * @output Contract for DELETE query DTO.
 * @modulemap
 * INTERFACE DeleteQueryInterface => DELETE query contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: DELETE, SQL, interface, query contract, delete, WithInterface, FromInterface, WhereInterface

// region INTERFACE_DeleteQueryInterface [DOMAIN(8): Query; CONCEPT(9): Delete; TECH(8): SQL]
/**
 * @purpose Contract composing WITH, FROM, and WHERE interfaces for DELETE queries.
 */
interface DeleteQueryInterface extends WithInterface, FromInterface, WhereInterface {}
// endregion INTERFACE_DeleteQueryInterface
