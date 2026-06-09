<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Grammar;

// region MODULE_CONTRACT [DOMAIN(7): Grammar; CONCEPT(6): QueryResult; TECH(7): DTO]
/**
 * @moduleContract
 * @purpose Holds the result of query building — a compiled SQL string with its bound parameters.
 * @scope Immutable DTO for SQL + params pairs.
 * @input string $sql, array $params
 * @output BuiltQuery instance
 * @invariants
 * - The object is read-only (readonly class with private(set) properties).
 * @modulemap
 * BuiltQuery => Result container for compiled SQL queries
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: BuiltQuery, SQL result, query params, compiled query
// STRUCTURE: ▶ ┌sql, params┐ → ∑ [BuiltQuery DTO]

// region CLASS_BuiltQuery [DOMAIN(7): Grammar; CONCEPT(6): QueryResult; TECH(7): DTO]
final readonly class BuiltQuery
{
    /**
     * @template TBuiltParam of bool|int|float|string|null
     *
     * @param string                         $sql
     * @param array<string|int, TBuiltParam> $params
     */
    public function __construct(
        private(set) string $sql,
        private(set) array $params = [],
    ) {}
}
// endregion CLASS_BuiltQuery
