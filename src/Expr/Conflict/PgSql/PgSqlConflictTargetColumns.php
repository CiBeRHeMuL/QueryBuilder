<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Conflict\PgSql;

use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\Conflict\ConflictTargetInterface;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(8): Conflict; CONCEPT(8): TargetColumns; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL conflict target by column list: (col1 [COLLATE coll] [opclass], (expr), ...) [WHERE predicate].
 * @scope ON CONFLICT (columns) clause target generation.
 * @input array $columns, ?array $where.
 * @output SQL string + params for column-based conflict target.
 * @invariants
 * - Each column is: string (name), ExprInterface (index expression), or array ['column' => ..., 'collate' => ..., 'opclass' => ...]
 * @modulemap
 * PgSqlConflictTargetColumns => Column-based conflict target
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, ON CONFLICT, columns, COLLATE, opclass, WHERE, conflict target
// STRUCTURE: ▶ getSql ┌grammar┐ → ○ loop $columns: ◇ string? ⚡ escapeIdentifier → ◇ ExprInterface? ⚡ wrap in (expr) → ◇ array? ⚡ escapeIdentifier + COLLATE + opclass → ⊕ colParts → ◇ has $where? ⚡ AndExpr WHERE → ∑ return '(...) [WHERE ...]'

// region CLASS_PgSqlConflictTargetColumns [DOMAIN(8): Conflict; CONCEPT(8): TargetColumns; TECH(8): Dialect]
/**
 * @purpose PostgreSQL implementation of ConflictTargetInterface — renders (col1 COLLATE "C" opclass, (expr), col2) [WHERE pred].
 */
final class PgSqlConflictTargetColumns implements ConflictTargetInterface
{
    private array $builtParams = [];

    /**
     * @param array<int, string|ExprInterface|array{column: string, collate?: string, opclass?: string}> $columns
     * @param array $where
     */
    public function __construct(
        private(set) array $columns = [],
        private(set) array $where = [],
    ) {}

    // region METHOD_getSql [DOMAIN(8): Conflict; TECH(8): SQLGeneration]
    /**
     * @purpose Render the column-based conflict target SQL.
     */
    public function getSql(GrammarInterface $grammar): string
    {
        $colParts = [];
        foreach ($this->columns as $column) {
            if (is_string($column)) {
                $colParts[] = $grammar->escapeIdentifier($column);
            } elseif ($column instanceof ExprInterface) {
                $colParts[] = '(' . $column->getExpression($grammar) . ')';
            } else {
                $escaped = $grammar->escapeIdentifier($column['column']);
                if (isset($column['collate'])) {
                    $escaped .= ' COLLATE ' . $grammar->escapeIdentifier($column['collate']);
                }
                if (isset($column['opclass'])) {
                    $escaped .= ' ' . $column['opclass'];
                }
                $colParts[] = $escaped;
            }
        }

        $sql = '(' . implode(', ', $colParts) . ')';

        if ($this->where) {
            $andExpr = new AndExpr($this->where);
            $sql .= ' WHERE ' . $andExpr->getExpression($grammar);
            $this->builtParams = $andExpr->getParams();
        }

        return $sql;
    }
    // endregion METHOD_getSql

    // region METHOD_getParams [DOMAIN(8): Conflict; TECH(8): ParameterExtraction]
    /**
     * @purpose Return params from WHERE conditions if present.
     */
    public function getParams(): array
    {
        return $this->builtParams;
    }
    // endregion METHOD_getParams
}
// endregion CLASS_PgSqlConflictTargetColumns
