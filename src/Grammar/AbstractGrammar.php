<?php

namespace AndrewGos\QueryBuilder\Grammar;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\Cte\Cycle;
use AndrewGos\QueryBuilder\Expr\Cte\Search;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\SetOperation\SetOperation;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Delete\DeleteQueryInterface;
use AndrewGos\QueryBuilder\Query\Insert\InsertQueryInterface;
use AndrewGos\QueryBuilder\Query\Interface\FromInterface;
use AndrewGos\QueryBuilder\Query\Interface\JoinInterface;
use AndrewGos\QueryBuilder\Query\Interface\LimitInterface;
use AndrewGos\QueryBuilder\Query\Interface\OperationsInterface;
use AndrewGos\QueryBuilder\Query\Interface\OrderByInterface;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Interface\WhereInterface;
use AndrewGos\QueryBuilder\Query\Interface\WithInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Update\UpdateQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use BackedEnum;
use UnitEnum;

abstract class AbstractGrammar implements GrammarInterface
{
    public function buildSelectQuery(SelectQueryInterface $query): BuiltQuery
    {
        $parts = [
            $this->buildWithClause($query),
            $this->buildSelectClause($query),
            $this->buildFromClause($query),
            $this->buildJoinClause($query),
            $this->buildWhereClause($query),
            $this->buildGroupByClause($query),
            $this->buildHavingClause($query),
            $this->buildWindowsClause($query),
            $this->buildOperationClauses($query),
            $this->buildOrderByClause($query),
            $this->buildLimitClause($query),
            $this->buildLockClause($query),
        ];

        $expr = HExpr::mergeExpressionParts($parts, $this, ' ');

        return new BuiltQuery(
            $expr->getExpression($this),
            $expr->getParams(),
        );
    }

    public function buildValuesQuery(ValuesQueryInterface $query): BuiltQuery
    {
        $parts = [
            new Expr('VALUES'),
            $this->buildValuesList($query),
            $this->buildOperationClauses($query),
            $this->buildOrderByClause($query),
            $this->buildLimitClause($query),
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
            default => throw QueryBuilderException::returnableQueryCannotBeBuilt($query, $this),
        };
    }

    public function buildDeleteQuery(DeleteQueryInterface $query): BuiltQuery
    {
        $parts = [
            $this->buildWithClause($query),
            new Expr('DELETE'),
            $this->buildFromClause($query),
            $this->buildWhereClause($query),
        ];

        $expr = HExpr::mergeExpressionParts($parts, $this, ' ');

        return new BuiltQuery(
            $expr->getExpression($this),
            $expr->getParams(),
        );
    }

    public function buildInsertQuery(InsertQueryInterface $query): BuiltQuery
    {
        // TODO: Implement buildInsertQuery() method.
    }

    public function buildUpdateQuery(UpdateQueryInterface $query): BuiltQuery
    {
        // TODO: Implement buildUpdateQuery() method.
    }

    public function escapeIdentifierDotted(string $identifier): string
    {
        $parts = explode('.', $identifier);
        return implode(
            '.',
            array_map(
                $this->escapeIdentifier(...),
                $parts,
            ),
        );
    }

    public function escapeTableAlias(string $alias): string
    {
        preg_match('/^([^(]+)(?:\(([^)]+)\))?$/ui', $alias, $matches);

        $tableAlias = $matches[1];
        $columns = $matches[2] ?? '';
        $columns = trim($columns);

        return sprintf(
            '%s%s%s%s',
            $this->escapeIdentifierDotted($tableAlias),
            $columns ? '(' : '',
            $columns
                ? implode(
                    ', ',
                    array_map(
                        $this->escapeIdentifier(...),
                        explode(',', $columns),
                    ),
                )
                : '',
            $columns ? ')' : '',
        );
    }

    protected function buildWithClause(WithInterface $query): ?ExprInterface
    {
        if ($query->with) {
            $parts = [];
            foreach ($query->with as $alias => $withQuery) {
                $parts[] = $this->buildWithQuery($alias, $withQuery);
            }

            $expr = HExpr::mergeExpressionParts($parts, $this, ' ');

            return new Expr(
                'WITH '
                . ($query->withRecursive ? 'RECURSIVE ' : '')
                . $expr->getExpression($this),
                $expr->getParams(),
            );
        }
        return null;
    }

    protected function buildWithQuery(string $alias, WithQuery $withQuery): ExprInterface
    {
        $parts = [
            $this->escapeTableAlias($alias),
            'AS',
            $this->buildWithQueryModifiers($alias, $withQuery),
            '(',
            $this->buildMaybeReturnableQuery($withQuery->query),
            ')',
            $withQuery->search ? $this->buildWithSearch($withQuery->search) : null,
            $withQuery->cycle ? $this->buildWithCycle($withQuery->cycle) : null,
        ];

        return HExpr::mergeExpressionParts($parts, $this, ' ');
    }

    protected function buildWithQueryModifiers(string $alias, WithQuery $withQuery): ?ExprInterface
    {
        return null;
    }

    protected function buildWithSearch(Search $search): ExprInterface
    {
        return new Expr(
            sprintf(
                'SEARCH %s FIRST BY %s SET %s',
                $search->type->getSql(),
                implode(
                    ', ',
                    array_map(
                        $this->escapeIdentifier(...),
                        $search->columns,
                    ),
                ),
                $this->escapeIdentifier($search->searchSeqColumnName),
            ),
        );
    }

    protected function buildWithCycle(Cycle $cycle): ExprInterface
    {
        $params = [];

        $cycleMarkValue = $cycle->cycleMarkValue->getExpression($this);
        $params = HExpr::mergeParams($params, $cycle->cycleMarkValue->getParams());
        $cycleMarkDefault = $cycle->cycleMarkDefault->getExpression($this);
        $params = HExpr::mergeParams($params, $cycle->cycleMarkDefault->getParams());

        return new Expr(
            sprintf(
                'CYCLE %s SET %s TO %s DEFAULT %s USING %s',
                implode(
                    ', ',
                    array_map(
                        $this->escapeIdentifier(...),
                        $cycle->columns,
                    ),
                ),
                $this->escapeIdentifier($cycle->cycleMarkColumnName),
                $cycleMarkValue,
                $cycleMarkDefault,
                $this->escapeIdentifier($cycle->cycleMarkColumnName),
            ),
            $params,
        );
    }

    protected function buildSelectClause(SelectQueryInterface $query): ExprInterface
    {
        $parts = [
            'SELECT',
            $this->buildDistinctClause($query),
            $this->buildSelectColumns($query->selectColumns),
        ];

        return HExpr::mergeExpressionParts($parts, $this, ' ');
    }

    protected function buildSelectColumns(array $selectColumns): ExprInterface
    {
        $parts = [];
        $params = [];
        $vb = new ValueBuilder();

        foreach ($selectColumns ?: ['*'] as $alias => $value) {
            HExpr::testSelectExpr($value);

            $builtValue = $value;
            if (is_string($value)) {
                $builtValue = $this->escapeIdentifierDotted($value);
            } else {
                $expr = $vb->build($value, $this);
                $builtValue = $expr->getExpression($this);
                $params = HExpr::mergeParams($params, $expr->getParams());
            }

            is_string($alias) && $builtValue .= ' AS ' . $this->escapeIdentifier($alias);

            $parts[] = $builtValue;
        }

        return new Expr(
            implode(', ', $parts),
            $params,
        );
    }

    protected function buildDistinctClause(SelectQueryInterface $query): ?ExprInterface
    {
        return $query->distinct ? new Expr($query->distinct) : null;
    }

    protected function buildFromClause(FromInterface $query): ?ExprInterface
    {
        if ($query->from) {
            $expr = $this->buildTables($query->from);
            return new Expr(
                'FROM ' . $expr->getExpression($this),
                $expr->getParams(),
            );
        }
        return null;
    }

    protected function buildTables(array $tables): ExprInterface
    {
        $parts = [];
        foreach ($tables as $alias => $table) {
            HExpr::testTable($table);
            $parts[] = $this->buildTable($alias, $table);
        }

        return HExpr::mergeExpressionParts($parts, $this, ', ');
    }

    protected function buildJoinClause(JoinInterface $query): ?ExprInterface
    {
        if ($query->joinTables) {
            $parts = [];
            $params = [];
            foreach ($query->joinTables as $name => $table) {
                $joinTypeStr = $table->type->getSql();

                $conditions = $table->conditions;
                if ($table->naturalJoin) {
                    $joinTypeStr = "NATURAL $joinTypeStr";
                    $conditions = [];
                }

                $builtTable = $this->buildTable($name, $table->table);

                $condition = null;
                if ($conditions) {
                    foreach ($conditions as $key => &$value) {
                        // If a value is string, then it's a column name, so we MUST escape it before building conditions
                        // because we use a common conditions builder for all conditions
                        if (is_string($value)) {
                            $value = new Expr($this->escapeIdentifierDotted($value));
                        }
                    }
                    $condition = $this->buildConditions($conditions);
                }

                $conditionExpr = $condition?->getExpression($this) ?? '';

                $parts[] = sprintf(
                    '%s %s %s',
                    $joinTypeStr,
                    $builtTable->getExpression($this),
                    $conditionExpr ? "ON $conditionExpr" : '',
                );
                $params = HExpr::mergeParams($params, $builtTable->getParams(), $condition?->getParams() ?: []);
            }

            return new Expr(
                implode(' ', $parts),
                $params,
            );
        }
        return null;
    }

    protected function buildWhereClause(WhereInterface $query): ?ExprInterface
    {
        if ($query->where) {
            $expr = $this->buildConditions($query->where);

            return new Expr(
                'WHERE ' . $expr->getExpression($this),
                $expr->getParams(),
            );
        }
        return null;
    }

    protected function buildTable(int|string $alias, SelectTable|ExprInterface|ValuesQueryInterface|SelectQueryInterface $table): ExprInterface
    {
        $vb = new ValueBuilder();
        $parts = [];

        $parts[] = $this->buildBeforeTableModifiers($alias, $table);

        if (!$table instanceof SelectTable) {
            $expr = $vb->build($table, $this);
            $parts[] = $expr->getExpression($this);
        } else {
            $parts[] = $this->escapeIdentifierDotted($table->name);
        }

        is_string($alias) && $parts[] = 'AS ' . $this->escapeTableAlias($alias);

        $parts[] = $this->buildAfterTableModifiers($alias, $table);

        return HExpr::mergeExpressionParts($parts, $this, ' ');
    }

    protected function buildBeforeTableModifiers(
        int|string $alias,
        SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface $table,
    ): ?ExprInterface {
        return null;
    }

    protected function buildAfterTableModifiers(
        int|string $alias,
        SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface $table,
    ): ?ExprInterface {
        return null;
    }

    protected function buildConditions(array $conditions): ExprInterface
    {
        return new AndExpr($conditions);
    }

    protected function buildGroupByClause(SelectQueryInterface $query): ?ExprInterface
    {
        if ($query->groupBy) {
            $expressions = [];
            $params = [];
            $vb = new ValueBuilder();
            foreach ($query->groupBy as $column) {
                if (is_string($column)) {
                    // Group by column name
                    $column = new Expr($this->escapeIdentifierDotted($column));
                } elseif (is_int($column)) {
                    // Group by ordinal column number
                    $column = new Expr("$column");
                } else {
                    $column = $vb->build($column, $this);
                }

                $expressions[] = $column->getExpression($this);
                $params = HExpr::mergeParams($params, $column->getParams());
            }

            return new Expr(
                'GROUP BY '
                . ($query->groupDistinct ? 'DISTINCT ' : '')
                . implode(', ', $expressions),
                $params,
            );
        }
        return null;
    }

    protected function buildHavingClause(SelectQueryInterface $query): ?ExprInterface
    {
        if ($query->groupBy && $query->having) {
            $expr = $this->buildConditions($query->having);

            return new Expr(
                'HAVING ' . $expr->getExpression($this),
                $expr->getParams(),
            );
        }
        return null;
    }

    protected function buildWindowsClause(SelectQueryInterface $query): ?ExprInterface
    {
        if ($query->windows) {
            $parts = [];
            $params = [];
            foreach ($query->windows as $name => $window) {
                $parts[] = sprintf(
                    '%s AS %s',
                    $this->escapeIdentifier($name),
                    $window->getExpression($this),
                );
                $params = HExpr::mergeParams($params, $window->getParams());
            }

            return new Expr(
                'WINDOW ' . implode(', ', $parts),
                $params,
            );
        }
        return null;
    }

    protected function buildOperationClauses(OperationsInterface $query): ?ExprInterface
    {
        if ($query->operations) {
            return HExpr::mergeExpressionParts(
                array_map(
                    $this->buildOperation(...),
                    $query->operations,
                ),
                $this,
                ' ',
            );
        }
        return null;
    }

    protected function buildOperation(SetOperation $operation): ExprInterface
    {
        $builtQuery = $this->buildSelectQuery($operation->query);

        return new Expr(
            sprintf(
                '%s (%s)',
                $operation->operation->getSql(),
                $builtQuery->sql,
            ),
            $builtQuery->params,
        );
    }

    protected function buildOrderByClause(OrderByInterface $query): ?ExprInterface
    {
        if ($query->orderBy) {
            $expressions = [];
            $params = [];
            $vb = new ValueBuilder();
            foreach ($query->orderBy as $column) {
                $sortType = $column->order;
                $sortBy = $column->expr;
                if (is_string($sortBy)) {
                    // Order by column name
                    $column = new Expr($this->escapeIdentifierDotted($sortBy));
                } elseif (is_int($sortBy)) {
                    // Order by ordinal column number
                    $column = new Expr("$sortBy");
                } else {
                    $column = $vb->build($sortBy, $this);
                }

                $expr = $column->getExpression($this);
                $expressions[] = "$expr $sortType";
                $params = HExpr::mergeParams($params, $column->getParams());
            }

            return new Expr(
                'ORDER BY ' . implode(', ', $expressions),
                $params,
            );
        }
        return null;
    }

    protected function buildLimitClause(LimitInterface $query): ?ExprInterface
    {
        $parts = [];
        $params = [];

        if ($query->offset) {
            $parts[] = 'OFFSET';
            if (is_int($query->offset)) {
                $parts[] = "$query->offset";
            } else {
                $parts[] = $query->offset->getExpression($this);
                $params = HExpr::mergeParams($params, $query->offset->getParams());
            }
        }

        if ($query->limit !== null) {
            $limit = $query->limit;
            $boundType = $query->limitBoundType;

            $parts[] = 'FETCH';

            $parts[] = $query->offset !== 0 ? 'NEXT' : 'FIRST';

            if (is_int($limit)) {
                $parts[] = "$limit";
            } else {
                $parts[] = "({$limit->getExpression($this)})";
                $params = $limit->getParams();
            }

            $parts[] = $limit === 1 ? 'ROW' : 'ROWS';

            $parts[] = $boundType->getSql();
        }

        if ($parts) {
            return new Expr(
                implode(' ', $parts),
                $params,
            );
        }
        return null;
    }

    protected function buildLockClause(SelectQueryInterface $query): ?ExprInterface
    {
        if ($query->lockMode !== null) {
            return new Expr(
                'FOR ' . $query->lockMode->getSql($this),
            );
        }
        return null;
    }

    protected function buildValuesList(ValuesQueryInterface $query): ExprInterface
    {
        $parts = [];
        $params = [];
        $vb = new ValueBuilder();
        foreach ($query->values as $value) {
            $expr = $vb->build($value, $this);
            $parts[] = '(' . $expr->getExpression($this) . ')';
            $params = HExpr::mergeParams($params, $expr->getParams());
        }

        return new Expr(
            implode(', ', $parts),
            $params,
        );
    }
}
