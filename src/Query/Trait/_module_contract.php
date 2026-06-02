<?php

/**
 * @moduleContract
 * @purpose Provide reusable trait implementations of Query clause interfaces.
 * @scope Each trait maps 1:1 to an Interface contract, enabling DRY composition in query classes.
 * @input Interface type parameters and expressions.
 * @output Concrete method implementations consumed by query classes.
 * @modulemap
 * FromTrait => FromInterface implementation (multi-table)
 * SingleFromTrait => FromInterface implementation (single-table, used by DELETE/UPDATE)
 * JoinTrait => JoinInterface implementation
 * LimitTrait => LimitInterface implementation
 * OperationsTrait => OperationsInterface implementation
 * OrderByTrait => OrderByInterface implementation
 * WhereTrait => WhereInterface implementation
 * WithTrait => WithInterface implementation
 * @usecases
 * - [Trait]: Query class → Use trait → Clause method implementation
 */
