<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose PostgreSQL-specific query traits including ReturningTrait for RETURNING clause implementation shared across INSERT/UPDATE/DELETE query types.
 * @scope PostgreSQL trait definitions.
 * @input Column expressions, OLD/NEW aliases
 * @output ReturningTrait for PostgreSQL RETURNING clause support in queries
 * @modulemap
 * ReturningTrait => PostgreSQL RETURNING clause implementation trait
 * @usecases
 * - ReturningTrait: Query → Use RETURNING clause → PostgreSQL DML with result return
 */
