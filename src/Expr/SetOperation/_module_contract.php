<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Provides the set operation value object for compound queries (UNION, INTERSECT, EXCEPT).
 * @scope Single value object linking an operation type with a SELECT query.
 * @input Set operation enum and SELECT query.
 * @output Set operation data for compound query rendering.
 * @modulemap
 * SetOperation => Set operation value object
 * @usecases
 * - [SetOperation]: Developer → Combine SELECT queries → UNION/INTERSECT/EXCEPT
 */
