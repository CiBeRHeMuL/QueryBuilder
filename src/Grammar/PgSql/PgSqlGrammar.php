<?php

namespace AndrewGos\QueryBuilder\Grammar\PgSql;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\Cte\PgSql\PgSqlWithQuery;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Expr\Table\PgSql\PgSqlSelectTable;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\BuiltQuery;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Delete\DeleteQueryInterface;
use AndrewGos\QueryBuilder\Query\Delete\PgSql\PgSqlDeleteQuery;
use AndrewGos\QueryBuilder\Query\Insert\InsertQueryInterface;
use AndrewGos\QueryBuilder\Query\Insert\PgSql\PgSqlInsertQuery;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Interface\PgSql\ReturningInterface;
use AndrewGos\QueryBuilder\Query\Select\PgSql\PgSqlSelectQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Grammar; CONCEPT(8): PgSqlGrammar; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL-specific SQL grammar implementing identifier escaping (double-quotes), CTE materialization, DISTINCT ON, ONLY table modifier, RETURNING clause, USING clause, and FOR UPDATE/SHARE lock modes.
 * @scope PostgreSQL SQL dialect query building.
 * @input Various query objects (SelectQueryInterface, DeleteQueryInterface, MaybeReturnableQueryInterface, etc.)
 * @output BuiltQuery with PostgreSQL dialect SQL and parameters
 * @invariants
 * - Identifiers are escaped with double quotes
 * - RETURNING clause only for queries implementing ReturningInterface
 * @modulemap
 * PgSqlGrammar => PostgreSQL SQL grammar implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, grammar, SQL, dialect, identifier escaping, DISTINCT ON, RETURNING, ONLY, USING, CTE, lock

// region CLASS_PgSqlGrammar [DOMAIN(8): Grammar; CONCEPT(8): PgSqlGrammar; TECH(8): Dialect]
/**
 * @purpose PostgreSQL dialect grammar extending AbstractGrammar with double-quote escaping, DISTINCT ON, ONLY modifier, RETURNING, USING clauses, and CTE materialization support.
 */
class PgSqlGrammar extends AbstractGrammar
{
    // region METHOD_buildDeleteQuery [DOMAIN(8): Grammar; TECH(8): Delete]
    /**
     * @purpose Build PostgreSQL-specific DELETE query with USING, JOIN, and RETURNING clause support.
     */
    public function buildDeleteQuery(DeleteQueryInterface $query): BuiltQuery
    {
        $parts = [
            $this->buildWithClause($query),
            'DELETE',
            $this->buildFromClause($query),
            $this->buildUsingClause($query),
            $this->buildWhereClause($query),
            $query instanceof ReturningInterface ? $this->buildReturning($query) : null,
        ];

        $expr = HExpr::mergeExpressionParts($parts, $this, ' ');

        return new BuiltQuery(
            $expr->getExpression($this),
            $expr->getParams(),
        );
    }
    // endregion METHOD_buildDeleteQuery

    // region METHOD_buildInsertQuery [DOMAIN(8): Grammar; TECH(8): Insert]
    /**
     * @purpose Build PostgreSQL INSERT query with OVERRIDING, ON CONFLICT, and RETURNING clause support.
     * STRUCTURE: ▶ ┌WITH, 'INSERT INTO', table, alias, columns, OVERRIDING, source, ON CONFLICT, RETURNING┐ → ● HExpr::merge → ∑ BuiltQuery
     */
    public function buildInsertQuery(InsertQueryInterface $query): BuiltQuery
    {
        $parts = [
            $this->buildWithClause($query),
            'INSERT INTO',
            $this->escapeIdentifierDotted($query->into),
            $query->alias !== null ? 'AS ' . $this->escapeTableAlias($query->alias) : null,
        ];

        if ($query->columnNames) {
            $parts[] = new Expr(
                '(' . implode(', ', array_map($this->escapeIdentifier(...), $query->columnNames)) . ')',
            );
        }

        if ($query instanceof PgSqlInsertQuery && $query->overrideValueMethod !== null) {
            $parts[] = new Expr('OVERRIDING ' . strtoupper($query->overrideValueMethod->name) . ' VALUE');
        }

        $parts[] = $this->buildInsertSource($query);

        if ($query instanceof PgSqlInsertQuery && $query->conflictAction !== null) {
            $parts[] = $this->buildConflictClause($query);
        }

        if ($query instanceof ReturningInterface) {
            $parts[] = $this->buildReturning($query);
        }

        $expr = HExpr::mergeExpressionParts($parts, $this, ' ');

        return new BuiltQuery(
            $expr->getExpression($this),
            $expr->getParams(),
        );
    }
    // endregion METHOD_buildInsertQuery

    // region METHOD_buildConflictClause [DOMAIN(8): Grammar; TECH(8): Conflict]
    /**
     * @purpose Build the ON CONFLICT clause: ON CONFLICT [target] action.
     * STRUCTURE: ┌'ON CONFLICT', conflictTarget->getSql, conflictAction->getSql┐ → ∑ Expr(implode(' '), params)
     */
    protected function buildConflictClause(PgSqlInsertQuery $query): ExprInterface
    {
        $targetSql = $query->conflictTarget?->getSql($this);
        $actionSql = $query->conflictAction->getSql($this);

        $parts = ['ON CONFLICT'];
        if ($targetSql !== null) {
            $parts[] = $targetSql;
        }
        $parts[] = $actionSql;

        $params = [];
        if ($query->conflictTarget !== null) {
            $params = HExpr::mergeParams($params, $query->conflictTarget->getParams());
        }
        $params = HExpr::mergeParams($params, $query->conflictAction->getParams());

        return new Expr(implode(' ', $parts), $params);
    }
    // endregion METHOD_buildConflictClause

    // region METHOD_buildMaybeReturnableQuery [DOMAIN(8): Grammar; TECH(8): QueryBuilding]
    /**
     * @purpose Route returnable query types (SELECT, VALUES, DELETE) to their respective builder methods.
     */
    public function buildMaybeReturnableQuery(MaybeReturnableQueryInterface $query): BuiltQuery
    {
        if (!$query->isReturnable()) {
            throw QueryBuilderException::valueIsNotReturnableQuery($query);
        }

        return match (true) {
            $query instanceof SelectQueryInterface => $this->buildSelectQuery($query),
            $query instanceof ValuesQueryInterface => $this->buildValuesQuery($query),
            $query instanceof DeleteQueryInterface => $this->buildDeleteQuery($query),
            default => throw QueryBuilderException::returnableQueryCannotBeBuilt($query, $this),
        };
    }
    // endregion METHOD_buildMaybeReturnableQuery

    // region METHOD_escapeIdentifier [DOMAIN(8): Grammar; TECH(8): IdentifierEscaping]
    /**
     * @purpose Escape identifier with PostgreSQL double quotes.
     */
    public function escapeIdentifier(string $identifier): string
    {
        if ($identifier === '*') {
            return $identifier;
        }
        $identifier = trim($identifier, " \n\r\t\v\0\"");
        return '"' . strtr($identifier, ['"' => '""']) . '"';
    }
    // endregion METHOD_escapeIdentifier

    // region METHOD_buildWithQueryModifiers [DOMAIN(8): Grammar; TECH(8): CTE]
    /**
     * @purpose Build PostgreSQL CTE modifiers: MATERIALIZED / NOT MATERIALIZED.
     */
    protected function buildWithQueryModifiers(string $alias, WithQuery $withQuery): ?ExprInterface
    {
        if ($withQuery instanceof PgSqlWithQuery) {
            if ($withQuery->materialized !== null) {
                return new Expr(
                    $withQuery->materialized
                        ? 'MATERIALIZED'
                        : 'NOT MATERIALIZED',
                );
            }
        }
        return parent::buildWithQueryModifiers($alias, $withQuery);
    }
    // endregion METHOD_buildWithQueryModifiers

    // region METHOD_buildDistinctClause [DOMAIN(8): Grammar; TECH(8): Distinct]
    /**
     * @purpose Build PostgreSQL DISTINCT clause, optionally extending with DISTINCT ON.
     */
    protected function buildDistinctClause(SelectQueryInterface $query): ?ExprInterface
    {
        if ($query->distinct) {
            $parts = [
                'DISTINCT',
                $query instanceof PgSqlSelectQuery ? $this->buildDistinctOn($query) : null,
            ];

            return HExpr::mergeExpressionParts($parts, $this, ' ');
        }
        return null;
    }
    // endregion METHOD_buildDistinctClause

    // region METHOD_buildDistinctOn [DOMAIN(8): Grammar; TECH(8): Distinct]
    /**
     * @purpose Build PostgreSQL DISTINCT ON (columns) clause.
     */
    protected function buildDistinctOn(PgSqlSelectQuery $query): ?ExprInterface
    {
        if ($query->distinctOn) {
            $expressions = [];
            $params = [];
            $vb = new ValueBuilder();
            foreach ($query->distinctOn as $expr) {
                if (is_string($expr)) {
                    // Distinct on by column name
                    $expr = new Expr($this->escapeIdentifierDotted($expr));
                } else {
                    $expr = $vb->build($expr, $this);
                }

                $expressions[] = $expr->getExpression($this);
                $params = HExpr::mergeParams($params, $expr->getParams());
            }

            return new Expr(
                'ON (' . implode(', ', $expressions) . ')',
                $params,
            );
        }
        return null;
    }
    // endregion METHOD_buildDistinctOn

    // region METHOD_buildBeforeTableModifiers [DOMAIN(8): Grammar; TECH(8): Table]
    /**
     * @purpose Build PostgreSQL ONLY modifier before table references for inheritance support.
     */
    protected function buildBeforeTableModifiers(
        int|string $alias,
        SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface $table,
    ): ?ExprInterface {
        if ($table instanceof PgSqlSelectTable && $table->only) {
            return new Expr('ONLY');
        }
        return parent::buildBeforeTableModifiers($alias, $table);
    }
    // endregion METHOD_buildBeforeTableModifiers

    // region METHOD_buildLockClause [DOMAIN(8): Grammar; TECH(8): Lock]
    /**
     * @purpose Build PostgreSQL FOR UPDATE / FOR NO KEY UPDATE / FOR SHARE / FOR KEY SHARE lock clause.
     */
    protected function buildLockClause(SelectQueryInterface $query): ?ExprInterface
    {
        if ($query instanceof PgSqlSelectQuery && $query->lockModes) {
            return new Expr(
                'FOR '
                . implode(
                    ' FOR ',
                    array_map(
                        fn(LockModeInterface $e) => $e->getSql($this),
                        $query->lockModes,
                    ),
                ),
            );
        }

        return parent::buildLockClause($query);
    }
    // endregion METHOD_buildLockClause

    // region METHOD_buildReturning [DOMAIN(8): Grammar; TECH(8): Returning]
    /**
     * @purpose Build PostgreSQL RETURNING clause with optional OLD AS / NEW AS aliases.
     */
    protected function buildReturning(ReturningInterface $query): ?ExprInterface
    {
        if ($query->returningColumns !== null) {
            $parts = ['RETURNING'];

            if ($query->returningOldAlias !== null || $query->returningNewAlias !== null) {
                $aliasParts = [];
                if ($query->returningOldAlias !== null) {
                    $aliasParts[] = 'OLD AS ' . $this->escapeIdentifier($query->returningOldAlias);
                }
                if ($query->returningNewAlias !== null) {
                    $aliasParts[] = 'NEW AS ' . $this->escapeIdentifier($query->returningNewAlias);
                }
                $parts[] = 'WITH (' . implode(', ', $aliasParts) . ') ';
            }

            $parts[] = $this->buildSelectColumns($query->returningColumns);

            return HExpr::mergeExpressionParts($parts, $this, ' ');
        }
        return null;
    }
    // endregion METHOD_buildReturning

    // region METHOD_buildUsingClause [DOMAIN(8): Grammar; TECH(8): Using]
    /**
     * @purpose Build PostgreSQL USING clause for DELETE queries with additional table references.
     */
    protected function buildUsingClause(DeleteQueryInterface $query): ?ExprInterface
    {
        if ($query instanceof PgSqlDeleteQuery && $query->using) {
            $parts = [
                new Expr('USING'),
                $this->buildTables($query->using),
                $this->buildJoinClause($query),
            ];

            return HExpr::mergeExpressionParts($parts, $this, ' ');
        }
        return null;
    }
    // endregion METHOD_buildUsingClause
}
// endregion CLASS_PgSqlGrammar
