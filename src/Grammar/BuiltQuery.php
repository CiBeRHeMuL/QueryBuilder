<?php

namespace AndrewGos\QueryBuilder\Grammar;

final readonly class BuiltQuery
{
    /**
     * @template TBuiltParam of bool|int|float|string|null
     *
     * @param string $sql
     * @param array<string|int, TBuiltParam> $params
     */
    public function __construct(
        private(set) string $sql,
        private(set) array $params = [],
    ) {}
}
