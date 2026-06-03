<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Conflict\PgSql;

use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\Conflict\ConflictActionInterface;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;

// region MODULE_CONTRACT [DOMAIN(8): Conflict; CONCEPT(8): ActionDoUpdate; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL conflict action DO UPDATE SET — renders "DO UPDATE SET col1 = val1, col2 = DEFAULT, ... WHERE condition".
 * @scope ON CONFLICT DO UPDATE SET clause action generation.
 * @input array $set (column => value), array $where (conditions)
 * @output SQL string + params for DO UPDATE SET action.
 * @invariants
 * - $set map: string column name => bool|int|float|string|ExprInterface|null value
 * - $where follows standard conditions array format
 * @modulemap
 * PgSqlConflictActionDoUpdate => DO UPDATE SET conflict action
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, ON CONFLICT, DO UPDATE, SET, WHERE, conflict action
// STRUCTURE: ▶ getSql ┌grammar┐ → ○ loop $set: ⚡ SetClause per column → ⊕ setParts + params → ◇ has $where? ⚡ AndExpr WHERE → ⊕ mergeParams → ∑ return 'DO UPDATE SET ... [WHERE ...]'

// region CLASS_PgSqlConflictActionDoUpdate [DOMAIN(8): Conflict; CONCEPT(8): ActionDoUpdate; TECH(8): Dialect]
/**
 * @purpose PostgreSQL implementation of ConflictActionInterface — renders DO UPDATE SET col = val, ... WHERE cond.
 */
final class PgSqlConflictActionDoUpdate implements ConflictActionInterface
{
    private array $builtParams = [];

    /**
     * @param array<string, bool|int|float|string|ExprInterface|null> $set
     * @param array $where
     */
    public function __construct(
        private(set) array $set,
        private(set) array $where = [],
    ) {}

    // region METHOD_getSql [DOMAIN(8): Conflict; TECH(8): SQLGeneration]
    /**
     * @purpose Render the DO UPDATE SET ... WHERE ... SQL fragment. Each SET column renders via SetClause::getSql().
     */
    public function getSql(GrammarInterface $grammar): string
    {
        $setParts = [];
        $params = [];

        foreach ($this->set as $column => $value) {
            $expr = (new SetClause($column, $value))->getSql($grammar);
            $setParts[] = $expr->getExpression($grammar);
            $params = HExpr::mergeParams($params, $expr->getParams());
        }

        $sql = 'DO UPDATE SET ' . implode(', ', $setParts);

        if ($this->where) {
            $andExpr = new AndExpr($this->where);
            $sql .= ' WHERE ' . $andExpr->getExpression($grammar);
            $params = HExpr::mergeParams($params, $andExpr->getParams());
        }

        $this->builtParams = $params;
        return $sql;
    }
    // endregion METHOD_getSql

    // region METHOD_getParams [DOMAIN(8): Conflict; TECH(8): ParameterExtraction]
    /**
     * @purpose Return params from SET values and WHERE conditions.
     */
    public function getParams(): array
    {
        return $this->builtParams;
    }
    // endregion METHOD_getParams
}
// endregion CLASS_PgSqlConflictActionDoUpdate
