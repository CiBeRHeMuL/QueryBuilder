<?php

/**
 * @moduleContract
 * @purpose PostgreSQL-specific query interfaces including RETURNING clause support for INSERT/UPDATE/DELETE queries.
 * @scope PostgreSQL query interface definitions.
 * @input Column expressions, OLD/NEW aliases
 * @output Interfaces for PostgreSQL RETURNING clause implementation
 * @modulemap
 * ReturningInterface => PostgreSQL RETURNING clause interface
 * @usecases
 * - ReturningInterface: QueryBuilder → Implement PostgreSQL RETURNING clause → DML with result return
 */
