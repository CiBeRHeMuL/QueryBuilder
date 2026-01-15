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
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Interface\PgSql\ReturningInterface;
use AndrewGos\QueryBuilder\Query\Select\PgSql\PgSqlSelectQuery;
use AndrewGos\QueryBuilder\Query\Delete\PgSql\PgSqlDeleteQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

class PgSqlGrammar extends AbstractGrammar
{
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

    public function escapeIdentifier(string $identifier): string
    {
        if ($identifier === '*') {
            return $identifier;
        }
        $identifier = trim($identifier, " \n\r\t\v\0\"");
        return '"' . strtr($identifier, ['"' => '""']) . '"';
    }

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

    protected function buildBeforeTableModifiers(
        int|string $alias,
        SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface $table,
    ): ?ExprInterface {
        if ($table instanceof PgSqlSelectTable && $table->only) {
            return new Expr('ONLY');
        }
        return parent::buildBeforeTableModifiers($alias, $table);
    }

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
}
