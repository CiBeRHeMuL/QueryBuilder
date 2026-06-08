<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Grammar;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\ColumnExpr;
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
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Interface\OperationsInterface;
use AndrewGos\QueryBuilder\Query\Interface\OrderByInterface;
use AndrewGos\QueryBuilder\Query\Interface\WhereInterface;
use AndrewGos\QueryBuilder\Query\Interface\WithInterface;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Update\UpdateQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(10): Grammar; CONCEPT(10): SQLBuilding; TECH(10): QueryCompilation]
/**
 * @moduleContract
 * @purpose Abstract base for SQL dialect grammars. Provides the standard query-building pipeline: decomposes query interfaces into SQL clauses and merges them into a BuiltQuery.
 * @scope SELECT, VALUES, DELETE, INSERT, UPDATE query building; WITH/CTE, JOIN, WHERE, GROUP BY, HAVING, WINDOW, SET operations, ORDER BY, LIMIT/OFFSET, LOCK clause building; identifier and table alias escaping.
 * @input Query interfaces (SelectQueryInterface, ValuesQueryInterface, DeleteQueryInterface, InsertQueryInterface, UpdateQueryInterface)
 * @output BuiltQuery (SQL string + params)
 * @invariants
 * - Each build* method corresponds to exactly one SQL clause or sub-clause.
 * - TODO stub methods (buildInsertQuery, buildUpdateQuery) throw RuntimeException when called.
 * - All escaping methods are public and can be used standalone.
 * @rationale
 * Q: Why is this abstract rather than an interface with default methods?
 * A: Template Method pattern — concrete grammars override specific clause builders (e.g., buildSelectClause) while reusing the pipeline.
 * @modulemap
 * AbstractGrammar => Abstract SQL grammar base class
 * @usecases
 * - [AbstractGrammar]: Grammar implementation → Build SELECT query → BuiltQuery
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: AbstractGrammar, grammar, SQL building, query compilation, SELECT, VALUES, CTE, JOIN, WHERE

// region CLASS_AbstractGrammar [DOMAIN(10): Grammar; CONCEPT(10): SQLBuilding; TECH(10): QueryCompilation]
abstract class AbstractGrammar implements GrammarInterface
{
    // region METHOD_buildSelectQuery [DOMAIN(10): Grammar; CONCEPT(10): SELECT; TECH(10): Pipeline]
    /**
     * @purpose Build a complete SELECT query by assembling all clauses (WITH, SELECT, FROM, JOIN, WHERE, GROUP BY, HAVING, WINDOW, SET ops, ORDER BY, LIMIT, LOCK) in order.
     * @io SelectQueryInterface -> BuiltQuery
     * @complexity 7
     * @using HExpr::mergeExpressionParts
     * STRUCTURE: ▶ ┌WITH, SELECT, FROM, JOIN, WHERE, GROUP BY, HAVING, WINDOW, OPS, ORDER BY, LIMIT, LOCK┐ → ● HExpr::merge(parts, ' ') → ∑ BuiltQuery(expr, params)
     */
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
    // endregion METHOD_buildSelectQuery

    // region METHOD_buildValuesQuery [DOMAIN(9): Grammar; CONCEPT(9): VALUES; TECH(9): Pipeline]
    /**
     * @purpose Build a VALUES query: VALUES keyword + value list + optional SET ops + ORDER BY + LIMIT.
     * @io ValuesQueryInterface -> BuiltQuery
     * @complexity 5
     * STRUCTURE: ▶ ┌'VALUES', buildValuesList, buildOperationClauses, buildOrderByClause, buildLimitClause┐ → ● merge → ∑ BuiltQuery
     */
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
    // endregion METHOD_buildValuesQuery

    // region METHOD_buildMaybeReturnableQuery [DOMAIN(9): Grammar; CONCEPT(8): Dispatch; TECH(8): Returnable]
    /**
     * @purpose Dispatch a MaybeReturnableQueryInterface to the appropriate build method (SELECT or VALUES) or throw if not returnable.
     * @io MaybeReturnableQueryInterface -> BuiltQuery
     * @complexity 4
     * @throws QueryBuilderException
     * STRUCTURE: ◇ isReturnable()? → N: ✗ throw | Y: ◇ instanceof Select → buildSelectQuery | ◇ instanceof Values → buildValuesQuery | ✗ throw
     */
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
    // endregion METHOD_buildMaybeReturnableQuery

    // region METHOD_buildDeleteQuery [DOMAIN(9): Grammar; CONCEPT(9): DELETE; TECH(9): Pipeline]
    /**
     * @purpose Build a DELETE query: WITH + DELETE + FROM + WHERE.
     * @io DeleteQueryInterface -> BuiltQuery
     * @complexity 4
     * STRUCTURE: ▶ ┌WITH, 'DELETE', FROM, WHERE┐ → ● merge → ∑ BuiltQuery
     */
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
    // endregion METHOD_buildDeleteQuery

    // region METHOD_buildInsertQuery [DOMAIN(9): Grammar; CONCEPT(9): INSERT; TECH(9): Pipeline]
    /**
     * @purpose Build a complete INSERT query: [WITH] INSERT INTO table [(columns)] {VALUES|SELECT|DEFAULT VALUES}.
     * @io InsertQueryInterface -> BuiltQuery
     * @complexity 5
     * @using HExpr::mergeExpressionParts, buildWithClause, buildInsertSource
     * STRUCTURE: ▶ ┌WITH, 'INSERT INTO', table, alias, columns, source┐ → ● HExpr::merge(parts, ' ') → ∑ BuiltQuery
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

        $parts[] = $this->buildInsertSource($query);

        $expr = HExpr::mergeExpressionParts($parts, $this, ' ');

        return new BuiltQuery(
            $expr->getExpression($this),
            $expr->getParams(),
        );
    }
    // endregion METHOD_buildInsertQuery

    // region METHOD_buildInsertSource [DOMAIN(9): Grammar; CONCEPT(9): INSERT; TECH(9): SourceBuilding]
    /**
     * @purpose Build the source part of INSERT: DEFAULT VALUES, VALUES (query), or (SELECT query).
     * @io InsertQueryInterface -> ExprInterface
     * @complexity 4
     * STRUCTURE: ◇ source === null → 'DEFAULT VALUES' | ◇ source instanceof ValuesQuery → buildValuesQuery | ◇ source instanceof SelectQuery → '(' + buildSelectQuery + ')'
     */
    protected function buildInsertSource(InsertQueryInterface $query): ExprInterface
    {
        if ($query->source === null) {
            return new Expr('DEFAULT VALUES');
        }

        if ($query->source instanceof ValuesQueryInterface) {
            $bq = $this->buildValuesQuery($query->source);
            return new Expr($bq->sql, $bq->params);
        }

        // SelectQueryInterface
        $bq = $this->buildSelectQuery($query->source);
        return new Expr('(' . $bq->sql . ')', $bq->params);
    }
    // endregion METHOD_buildInsertSource

    // region METHOD_buildUpdateQuery [DOMAIN(9): Grammar; CONCEPT(9): UPDATE; TECH(9): Pipeline]
    /**
     * @purpose Build a complete UPDATE query (ANSI SQL): WITH + UPDATE + table + SET + WHERE. Validates that table is set.
     * @param UpdateQueryInterface $query The UPDATE query DTO with table, set, and optional where/with.
     * @return BuiltQuery The compiled SQL string and bound parameters.
     * @throws QueryBuilderException if table is empty
     * @complexity 5
     * STRUCTURE: ▶ ┌WITH, 'UPDATE', table, SET, WHERE┐ → ● HExpr::merge → ◇ empty table? → ✗ throw → ∑ BuiltQuery
     */
    public function buildUpdateQuery(UpdateQueryInterface $query): BuiltQuery
    {
        if ($query->table === '') {
            throw new QueryBuilderException('UPDATE query requires a table name. Call table() before building.');
        }

        if (!$query->set) {
            throw new QueryBuilderException('UPDATE query requires at least one SET clause. Call set() before building.');
        }

        $parts = [
            $this->buildWithClause($query),
            'UPDATE',
            $this->escapeIdentifierDotted($query->table),
            $this->buildSetClause($query->set),
            $this->buildWhereClause($query),
        ];

        $expr = HExpr::mergeExpressionParts($parts, $this, ' ');

        return new BuiltQuery(
            $expr->getExpression($this),
            $expr->getParams(),
        );
    }
    // endregion METHOD_buildUpdateQuery

    // region METHOD_buildSetClause [DOMAIN(9): Grammar; CONCEPT(9): SET; TECH(9): ClauseBuilder]
    /**
     * @purpose Build the SET clause from an array of SetClause objects. Delegates rendering to SetClause::getSql(), adds "SET " prefix.
     * @param SetClause[] $set Array of SetClause objects.
     * @return ExprInterface The rendered SET clause including "SET " prefix, with merged params.
     * @complexity 4
     * STRUCTURE: ┌set array┐ → ○ foreach $clause->getSql($this) → ⊕ parts, merge params → ∑ 'SET ' + implode(', ')
     */
    public function buildSetClause(array $set): ExprInterface
    {
        $parts = [];
        $params = [];

        foreach ($set as $clause) {
            $expr = $clause->getSql($this);
            $parts[] = $expr->getExpression($this);
            $params = HExpr::mergeParams($params, $expr->getParams());
        }

        return new Expr('SET ' . implode(', ', $parts), $params);
    }
    // endregion METHOD_buildSetClause

    // region METHOD_escapeIdentifierDotted [DOMAIN(8): Grammar; CONCEPT(8): Escaping; TECH(8): Identifier]
    /**
     * @purpose Escape a dotted identifier (e.g., `table.column`) by splitting on `.` and escaping each part.
     * @io string -> string
     * @complexity 3
     * STRUCTURE: explode('.', identifier) → array_map(escapeIdentifier) → implode('.')
     */
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
    // endregion METHOD_escapeIdentifierDotted

    // region METHOD_escapeTableAlias [DOMAIN(8): Grammar; CONCEPT(8): Escaping; TECH(8): Alias]
    /**
     * @purpose Escape a table alias with optional column list, e.g., `table(col1, col2)`.
     * @io string -> string
     * @complexity 6
     * STRUCTURE: preg_match('table(columns)') → escapeIdentifier(tableAlias) → ┌columns?┐ → Y: escapeIdentifier each → implode(', ') wrap in () | N: → ∑ result
     */
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

    // endregion METHOD_escapeTableAlias

    // region METHOD_buildWithClause [DOMAIN(9): Grammar; CONCEPT(9): CTE; TECH(9): WITH]
    /**
     * @purpose Build the WITH clause — iterate CTE aliases, build each WithQuery, merge, and prefix with WITH (optionally RECURSIVE).
     * @io WithInterface -> ?ExprInterface (null if no CTEs)
     * @complexity 6
     * STRUCTURE: ◇ with? → N: null | Y: ○ foreach alias → buildWithQuery ⊕ parts → 'WITH' + (Recursive?) + merge(parts) → ∑ Expr
     */
    protected function buildWithClause(WithInterface $query): ?ExprInterface
    {
        if ($query->with) {
            $parts = [];
            foreach ($query->with as $alias => $withQuery) {
                $parts[] = $this->buildWithQuery($alias, $withQuery);
            }

            $expr = HExpr::mergeExpressionParts($parts, $this, ', ');

            return new Expr(
                'WITH '
                . ($query->withRecursive ? 'RECURSIVE ' : '')
                . $expr->getExpression($this),
                $expr->getParams(),
            );
        }
        return null;
    }
    // endregion METHOD_buildWithClause

    // region METHOD_buildWithQuery [DOMAIN(9): Grammar; CONCEPT(9): CTE; TECH(9): WithQuery]
    /**
     * @purpose Build a single CTE: alias AS (modifiers) (query) [SEARCH] [CYCLE].
     * @io string alias, WithQuery -> ExprInterface
     * @complexity 5
     * STRUCTURE: ┌alias, 'AS', modifiers, '(', buildMaybeReturnableQuery, ')', SEARCH?, CYCLE?┐ → ● merge
     */
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
    // endregion METHOD_buildWithQuery

    // region METHOD_buildWithQueryModifiers [DOMAIN(8): Grammar; CONCEPT(8): CTE; TECH(8): Hook]
    /**
     * @purpose Hook for subclasses to add CTE modifiers (e.g., MATERIALIZED, NOT MATERIALIZED). Default returns null.
     * @io string alias, WithQuery -> ?ExprInterface
     * @complexity 1
     */
    protected function buildWithQueryModifiers(string $alias, WithQuery $withQuery): ?ExprInterface
    {
        return null;
    }
    // endregion METHOD_buildWithQueryModifiers

    // region METHOD_buildWithSearch [DOMAIN(9): Grammar; CONCEPT(8): CTE; TECH(9): SEARCH]
    /**
     * @purpose Build the SEARCH clause for recursive CTEs: `SEARCH {BREADTH|DEPTH} FIRST BY columns SET seq_column`.
     * @io Search -> ExprInterface
     * @complexity 4
     */
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
    // endregion METHOD_buildWithSearch

    // region METHOD_buildWithCycle [DOMAIN(9): Grammar; CONCEPT(8): CTE; TECH(9): CYCLE]
    /**
     * @purpose Build the CYCLE clause for recursive CTEs: `CYCLE columns SET mark_col TO value DEFAULT default_val USING mark_col`.
     * @io Cycle -> ExprInterface
     * @complexity 6
     * STRUCTURE: build cycleMarkValue + cycleMarkDefault → sprintf template → ∑ Expr with params
     */
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
    // endregion METHOD_buildWithCycle

    // region METHOD_buildSelectClause [DOMAIN(10): Grammar; CONCEPT(9): SELECT; TECH(9): Clause]
    /**
     * @purpose Build the SELECT clause: SELECT keyword + DISTINCT + columns.
     * @io SelectQueryInterface -> ExprInterface
     * @complexity 3
     * STRUCTURE: ┌'SELECT', buildDistinctClause, buildSelectColumns┐ → ● merge
     */
    protected function buildSelectClause(SelectQueryInterface $query): ExprInterface
    {
        $parts = [
            'SELECT',
            $this->buildDistinctClause($query),
            $this->buildSelectColumns($query->selectColumns),
        ];

        return HExpr::mergeExpressionParts($parts, $this, ' ');
    }
    // endregion METHOD_buildSelectClause

    // region METHOD_buildSelectColumns [DOMAIN(10): Grammar; CONCEPT(9): SELECT; TECH(9): ColumnList]
    /**
     * @purpose Build the comma-separated list of SELECT columns with optional aliases. Defaults to '*' if empty.
     * @io array -> ExprInterface
     * @complexity 7
     * STRUCTURE: ┌columns?┐ → N: '*' | Y: ○ foreach: 〈is_string? T: escapeIdentifier | F: ValueBuilder.build〉+ alias → ⊕ parts → ∑ Expr(implode(', '), params)
     */
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
    // endregion METHOD_buildSelectColumns

    // region METHOD_buildDistinctClause [DOMAIN(8): Grammar; CONCEPT(7): SELECT; TECH(7): DISTINCT]
    /**
     * @purpose Build the DISTINCT clause — returns the DISTINCT expression or null.
     * @io SelectQueryInterface -> ?ExprInterface
     * @complexity 2
     */
    protected function buildDistinctClause(SelectQueryInterface $query): ?ExprInterface
    {
        return $query->distinct ? new Expr($query->distinct) : null;
    }
    // endregion METHOD_buildDistinctClause

    // region METHOD_buildFromClause [DOMAIN(9): Grammar; CONCEPT(9): FROM; TECH(9): Clause]
    /**
     * @purpose Build the FROM clause with table list. Returns null if no FROM tables.
     * @io FromInterface -> ?ExprInterface
     * @complexity 3
     */
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
    // endregion METHOD_buildFromClause

    // region METHOD_buildTables [DOMAIN(9): Grammar; CONCEPT(9): FROM; TECH(9): TableList]
    /**
     * @purpose Build a comma-separated list of tables for FROM clause.
     * @io array -> ExprInterface
     * @complexity 4
     * STRUCTURE: ○ foreach table → buildTable ⊕ parts → merge(', ')
     */
    protected function buildTables(array $tables): ExprInterface
    {
        $parts = [];
        foreach ($tables as $alias => $table) {
            HExpr::testTable($table);
            $parts[] = $this->buildTable($alias, $table);
        }

        return HExpr::mergeExpressionParts($parts, $this, ', ');
    }
    // endregion METHOD_buildTables

    // region METHOD_buildJoinClause [DOMAIN(9): Grammar; CONCEPT(9): JOIN; TECH(9): Clause]
    /**
     * @purpose Build JOIN clauses — iterate join tables, handle NATURAL joins, build conditions per join.
     * @io JoinInterface -> ?ExprInterface
     * @complexity 8
     * STRUCTURE: ◇ joinTables? → N: null | Y: ○ foreach table → buildTable + conditions → sprintf('JOIN_TYPE table ON condition') ⊕ parts → ∑ Expr(implode(' '), params)
     */
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
                if ($table->type === JoinTypeEnum::CrossJoin) {
                    $conditions = [];
                }

                $builtTable = $this->buildTable($name, $table->table);

                $condition = null;
                if ($conditions) {
                    foreach ($conditions as $key => &$value) {
                        // If a value is string, then it's a column name, so we MUST escape it before building conditions
                        // because we use a common conditions builder for all conditions
                        if (is_string($value)) {
                            $value = new ColumnExpr($this->escapeIdentifierDotted($value));
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
    // endregion METHOD_buildJoinClause

    // region METHOD_buildWhereClause [DOMAIN(9): Grammar; CONCEPT(9): WHERE; TECH(9): Clause]
    /**
     * @purpose Build the WHERE clause from conditions array.
     * @io WhereInterface -> ?ExprInterface
     * @complexity 3
     * STRUCTURE: ◇ where? → N: null | Y: buildConditions → 'WHERE ' + expr → ∑ Expr
     */
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
    // endregion METHOD_buildWhereClause

    // region METHOD_buildTable [DOMAIN(9): Grammar; CONCEPT(9): Table; TECH(9): Building]
    /**
     * @purpose Build a table reference with optional alias and before/after modifiers.
     * @io int|string alias, table -> ExprInterface
     * @complexity 6
     * STRUCTURE: ┌beforeModifiers, build(table), 'AS alias', afterModifiers┐ → ● merge
     */
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
    // endregion METHOD_buildTable

    // region METHOD_buildBeforeTableModifiers [DOMAIN(8): Grammar; CONCEPT(8): Table; TECH(8): Hook]
    /**
     * @purpose Hook for subclasses to add modifiers before table reference (e.g., ONLY for PostgreSQL). Default null.
     * @io int|string alias, table -> ?ExprInterface
     * @complexity 1
     */
    protected function buildBeforeTableModifiers(
        int|string $alias,
        SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface $table,
    ): ?ExprInterface {
        return null;
    }
    // endregion METHOD_buildBeforeTableModifiers

    // region METHOD_buildAfterTableModifiers [DOMAIN(8): Grammar; CONCEPT(8): Table; TECH(8): Hook]
    /**
     * @purpose Hook for subclasses to add modifiers after table reference (e.g., WITH CHECK OPTION). Default null.
     * @io int|string alias, table -> ?ExprInterface
     * @complexity 1
     */
    protected function buildAfterTableModifiers(
        int|string $alias,
        SelectTable|ExprInterface|SelectQueryInterface|ValuesQueryInterface $table,
    ): ?ExprInterface {
        return null;
    }
    // endregion METHOD_buildAfterTableModifiers

    // region METHOD_buildConditions [DOMAIN(9): Grammar; CONCEPT(9): Conditions; TECH(9): AndExpr]
    /**
     * @purpose Build conditions array into an AND-joined expression.
     * @io array -> ExprInterface
     * @complexity 2
     */
    protected function buildConditions(array $conditions): ExprInterface
    {
        return new AndExpr($conditions);
    }
    // endregion METHOD_buildConditions

    // region METHOD_buildGroupByClause [DOMAIN(9): Grammar; CONCEPT(9): GROUP_BY; TECH(9): Clause]
    /**
     * @purpose Build the GROUP BY clause with optional DISTINCT modifier.
     * @io SelectQueryInterface -> ?ExprInterface
     * @complexity 6
     * STRUCTURE: ◇ groupBy? → N: null | Y: ○ foreach → string: escapeIdentifier | int: literal | else: ValueBuilder → ⊕ expressions → ∑ 'GROUP BY' + (DISTINCT?) + implode(', ')
     */
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
    // endregion METHOD_buildGroupByClause

    // region METHOD_buildHavingClause [DOMAIN(9): Grammar; CONCEPT(8): HAVING; TECH(9): Clause]
    /**
     * @purpose Build the HAVING clause — only rendered when both GROUP BY and HAVING are present.
     * @io SelectQueryInterface -> ?ExprInterface
     * @complexity 3
     */
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
    // endregion METHOD_buildHavingClause

    // region METHOD_buildWindowsClause [DOMAIN(9): Grammar; CONCEPT(8): WINDOW; TECH(9): Clause]
    /**
     * @purpose Build the WINDOW clause with named window definitions.
     * @io SelectQueryInterface -> ?ExprInterface
     * @complexity 5
     * STRUCTURE: ◇ windows? → N: null | Y: ○ foreach name → sprintf('%s AS %s', name, window.getExpression) ⊕ parts → ∑ 'WINDOW ' + implode(', ')
     */
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
    // endregion METHOD_buildWindowsClause

    // region METHOD_buildOperationClauses [DOMAIN(9): Grammar; CONCEPT(8): SET; TECH(9): Operations]
    /**
     * @purpose Build SET operation clauses (UNION, INTERSECT, EXCEPT) — map each operation to buildOperation and merge.
     * @io OperationsInterface -> ?ExprInterface
     * @complexity 4
     * STRUCTURE: ◇ operations? → N: null | Y: array_map(buildOperation) → merge(' ')
     */
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
    // endregion METHOD_buildOperationClauses

    // region METHOD_buildOperation [DOMAIN(9): Grammar; CONCEPT(8): SET; TECH(9): SingleOperation]
    /**
     * @purpose Build a single SET operation: `OPERATION (subquery)`.
     * @io SetOperation -> ExprInterface
     * @complexity 4
     */
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
    // endregion METHOD_buildOperation

    // region METHOD_buildOrderByClause [DOMAIN(9): Grammar; CONCEPT(9): ORDER_BY; TECH(9): Clause]
    /**
     * @purpose Build the ORDER BY clause with sort direction for each column.
     * @io OrderByInterface -> ?ExprInterface
     * @complexity 6
     * STRUCTURE: ◇ orderBy? → N: null | Y: ○ foreach → string: escapeIdentifier | int: literal | else: ValueBuilder → 'expr SORT_TYPE' ⊕ → ∑ 'ORDER BY ' + implode(', ')
     */
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
    // endregion METHOD_buildOrderByClause

    // region METHOD_buildLimitClause [DOMAIN(9): Grammar; CONCEPT(9): LIMIT; TECH(9): Clause]
    /**
     * @purpose Build the LIMIT/OFFSET clause using FETCH {FIRST|NEXT} syntax.
     * @io LimitInterface -> ?ExprInterface
     * @complexity 7
     * STRUCTURE: ┌OFFSET ┤int|expr├┐ + ┌FETCH {NEXT|FIRST} ┤int|(expr)├ {ROW|ROWS} boundType┐ → ∑ Expr(implode(' '), params) | null if empty
     */
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
    // endregion METHOD_buildLimitClause

    // region METHOD_buildLockClause [DOMAIN(9): Grammar; CONCEPT(8): Lock; TECH(9): Clause]
    /**
     * @purpose Build the FOR UPDATE / FOR SHARE locking clause.
     * @io SelectQueryInterface -> ?ExprInterface
     * @complexity 2
     */
    protected function buildLockClause(SelectQueryInterface $query): ?ExprInterface
    {
        if ($query->lockMode !== null) {
            return new Expr(
                'FOR ' . $query->lockMode->getSql($this),
            );
        }
        return null;
    }
    // endregion METHOD_buildLockClause

    // region METHOD_buildValuesList [DOMAIN(9): Grammar; CONCEPT(9): VALUES; TECH(9): RowList]
    /**
     * @purpose Build the VALUES row list — each row value is parenthesized and comma-separated.
     * @io ValuesQueryInterface -> ExprInterface
     * @complexity 5
     * STRUCTURE: ○ foreach value → ValueBuilder.build → '(' + expr + ')' ⊕ parts → ∑ Expr(implode(', '), params)
     */
    protected function buildValuesList(ValuesQueryInterface $query): ExprInterface
    {
        $parts = [];
        $params = [];
        $vb = new ValueBuilder();
        foreach ($query->values as $value) {
            $expr = $vb->build($value, $this);
            if (is_array($value)) {
                $parts[] = $expr->getExpression($this);
            } else {
                $parts[] = '(' . $expr->getExpression($this) . ')';
            }
            $params = HExpr::mergeParams($params, $expr->getParams());
        }

        return new Expr(
            implode(', ', $parts),
            $params,
        );
    }
    // endregion METHOD_buildValuesList
}
// endregion CLASS_AbstractGrammar
