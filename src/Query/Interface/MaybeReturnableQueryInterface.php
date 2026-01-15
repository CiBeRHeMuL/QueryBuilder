<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

/**
 * This interface indicates that query can return values after execution.
 * For example: SELECT query, VALUES query, queries with RETURNING in PostgreSQL
 */
interface MaybeReturnableQueryInterface
{
    public function isReturnable(): bool;
}
