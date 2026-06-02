<?php

/**
 * @moduleContract
 * @purpose Define clause interfaces for SQL query building (FROM, JOIN, WHERE, ORDER BY, LIMIT, etc.).
 * @scope Stateless contract interfaces for each SQL clause.
 * @input Generic type parameters for type-safe query construction.
 * @output Interface contracts implemented by query classes and traits.
 * @modulemap
 * FromInterface => FROM clause contract
 * JoinInterface => JOIN clause contract (inner, left, right, cross, full, natural)
 * LimitInterface => OFFSET / LIMIT / FETCH clause contract
 * MaybeReturnableQueryInterface => Marks queries that can return values
 * OperationsInterface => UNION / INTERSECT / EXCEPT clause contract
 * OrderByInterface => ORDER BY clause contract
 * WhereInterface => WHERE clause contract
 * WithInterface => WITH (CTE) clause contract
 * @usecases
 * - [ClauseInterface]: Query class → Implement interface → SQL clause rendering
 */
