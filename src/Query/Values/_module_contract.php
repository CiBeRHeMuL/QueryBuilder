<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Provide VALUES SQL query construction for inline value lists with ORDER BY, LIMIT, and set operations.
 * @scope ValuesQueryInterface and its implementation ValuesQuery.
 * @input Nested value arrays.
 * @output SQL VALUES query DTO (returnable).
 * @modulemap
 * ValuesQueryInterface => VALUES query contract (extends OperationsInterface, OrderByInterface, LimitInterface, MaybeReturnableQueryInterface)
 * ValuesQuery => VALUES query implementation (uses OperationsTrait, OrderByTrait, LimitTrait)
 * @usecases
 * - [ValuesQuery]: Developer → Build VALUES clause → SQL VALUES statement
 */
