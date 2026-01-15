<?php

namespace AndrewGos\QueryBuilder\Expr\Insert\Pg\ConflictAction;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Insert\Pg\ConflictAction\PgSqlConflictActionInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Query\Interface\WhereInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Trait\WhereTrait;
use UnitEnum;

/**
 * This class allow you to use DO UPDATE clause in INSERT conflict action
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @template TExpression of TValue|array<TExpression>
 */
class DoUpdate implements PgSqlConflictActionInterface, WhereInterface
{
    use WhereTrait;

    /**
     * @param array<string, TExpression> $columns
     */
    public function __construct(protected(set) array $columns) {}

    public function getSql(GrammarInterface $grammar): ExprInterface
    {

    }
}
