<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Define the Query namespace contracts for SQL query building (SELECT, INSERT, UPDATE, DELETE, VALUES).
 * @scope Sub-namespace partitioning: Delete, Insert, Interface, Select, Trait, Update, Values.
 * @input Query builder configuration and expression objects.
 * @output Immutable query DTOs ready for SQL rendering.
 * @modulemap
 * Delete/ => DELETE query construction
 * Insert/ => INSERT query construction
 *  Interface/ => Clause interfaces (FROM, JOIN, WHERE, ORDER BY, LIMIT, WITH, Operations, Partition, Returning)
 * Select/ => SELECT query construction
 * Trait/ => Interface implementations as reusable traits
 * Update/ => UPDATE query construction
 * Values/ => VALUES query construction
 * @usecases
 * - [QueryBuilder]: Developer → Build SQL query → Rendered SQL string
 */
