<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;

interface WithInterface
{
    /**
     * @var array<string, WithQuery>
     */
    public array $with {
        get;
    }
    public bool $withRecursive {
        get;
    }

    /**
     * @param array<string, WithQuery> $with
     * @param bool $recursive
     *
     * @return static
     */
    public function with(array $with, bool $recursive = false): static;

    /**
     * @param array<string, WithQuery> $with
     * @param bool $recursive
     *
     * @return static
     */
    public function addWith(array $with, bool $recursive = false): static;
}
