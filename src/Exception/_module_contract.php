<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Exception namespace — centralized domain exception factory for the QueryBuilder.
 * @scope Error handling, value validation errors, lifecycle errors.
 * @input Error context (values, types, objects, grammar)
 * @output QueryBuilderException instances
 * @modulemap
 * QueryBuilderException [10][Domain exception with static named constructors] => QueryBuilderException.php
 * @usecases
 * - QueryBuilderException: Any module → Validation failure → Throw exception
 */
