<?php

namespace AndrewGos\QueryBuilder\Expr\Join;

use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface as SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): JOIN; CONCEPT(9): TableJoin; TECH(5): ValueObject]
/**
 * @moduleContract
 * @purpose Defines a single JOIN clause with type, target table, conditions, and natural join flag.
 * @scope Value object for join table configuration.
 * @input Join type, table reference, conditions array, natural join flag.
 * @output Join clause data for SQL rendering.
 * @modulemap
 * JoinTable => Join clause value object
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: JOIN, INNER, LEFT, RIGHT, CROSS, NATURAL, table join, SQL

// region CLASS_JoinTable [DOMAIN(8): JOIN; CONCEPT(9): TableJoin; TECH(5): ValueObject]
/**
 * @purpose Defines a single JOIN clause with type, target table, and conditions.
 */
final readonly class JoinTable
{
    /**
     * @param JoinTypeEnum $type
     * @param SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface $table
     * @param ExprInterface[] $conditions
     * @param bool $naturalJoin
     */
    public function __construct(
        private(set) JoinTypeEnum $type,
        private(set) SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface $table,
        private(set) array $conditions = [],
        private(set) bool $naturalJoin = false,
    ) {}
}
// endregion CLASS_JoinTable
