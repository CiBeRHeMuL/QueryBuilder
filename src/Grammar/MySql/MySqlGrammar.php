<?php

namespace AndrewGos\QueryBuilder\Grammar\MySql;

use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\BuiltQuery;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Delete\DeleteQueryInterface;
use AndrewGos\QueryBuilder\Query\Delete\MySql\MySqlDeleteQuery;
use AndrewGos\QueryBuilder\Query\Insert\InsertQueryInterface;
use AndrewGos\QueryBuilder\Query\Insert\MySql\MySqlInsertQuery;
use AndrewGos\QueryBuilder\Query\Interface\LimitInterface;
use AndrewGos\QueryBuilder\Query\Interface\MySql\PartitionInterface;
use AndrewGos\QueryBuilder\Query\Select\MySql\MySqlSelectQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Grammar; CONCEPT(8): MySqlGrammar; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose MySQL-specific SQL grammar implementing identifier escaping (backtick), DELETE/SELECT query building with MySQL extensions (LOW_PRIORITY, QUICK, IGNORE, HIGH_PRIORITY, SQL_* hints, LIMIT offset,syntax, PARTITION, FOR UPDATE/SHARE).
 * @scope MySQL SQL dialect query building.
 * @input Various query objects (SelectQueryInterface, DeleteQueryInterface, etc.)
 * @output BuiltQuery with MySQL dialect SQL and parameters
 * @invariants
 * - Identifiers are escaped with backticks
 * - LIMIT clause uses MySQL offset, count syntax
 * @modulemap
 * MySqlGrammar => MySQL SQL grammar implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MySQL, grammar, SQL, dialect, identifier escaping, LIMIT, PARTITION, lock

// region CLASS_MySqlGrammar [DOMAIN(8): Grammar; CONCEPT(8): MySqlGrammar; TECH(8): Dialect]
/**
 * @purpose MySQL dialect grammar extending AbstractGrammar with backtick escaping, MySQL-specific DELETE/SELECT options, and PARTITION clause support.
 */
class MySqlGrammar extends AbstractGrammar
{
    // region METHOD_escapeIdentifier [DOMAIN(8): Grammar; TECH(8): IdentifierEscaping]
    /**
     * @purpose Escape identifier with MySQL backticks.
     */
    public function escapeIdentifier(string $identifier): string
    {
        if ($identifier === '*') {
            return $identifier;
        }
        $identifier = trim($identifier, " \n\r\t\v\0`");
        return '`' . strtr($identifier, ['`' => '``']) . '`';
    }
    // endregion METHOD_escapeIdentifier

    // region METHOD_buildDeleteQuery [DOMAIN(8): Grammar; TECH(8): Delete]
    /**
     * @purpose Build MySQL-specific DELETE query with LOW_PRIORITY, QUICK, IGNORE, and PARTITION support.
     */
    public function buildDeleteQuery(DeleteQueryInterface $query): BuiltQuery
    {
        $parts = [
            $this->buildWithClause($query),
            'DELETE',
        ];
        if ($query instanceof MySqlDeleteQuery) {
            $query->lowPriority && $parts[] = new Expr('LOW_PRIORITY');
            $query->quick && $parts[] = new Expr('QUICK');
            $query->ignore && $parts[] = new Expr('IGNORE');
        }

        $parts = [
            ...$parts,
            $this->buildFromClause($query),
            $this->buildWhereClause($query),
            $query instanceof PartitionInterface ? $this->buildPartition($query) : null,
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
     * @purpose Build MySQL INSERT query with LOW_PRIORITY, DELAYED, HIGH_PRIORITY, IGNORE modifiers and PARTITION support.
     * STRUCTURE: ▶ ┌WITH, 'INSERT', modifiers, 'INTO', table, alias, columns, source, PARTITION┐ → ● HExpr::merge → ∑ BuiltQuery
     */
    public function buildInsertQuery(InsertQueryInterface $query): BuiltQuery
    {
        $parts = [
            $this->buildWithClause($query),
            'INSERT',
        ];

        if ($query instanceof MySqlInsertQuery) {
            $query->lowPriority && $parts[] = new Expr('LOW_PRIORITY');
            $query->delayed && $parts[] = new Expr('DELAYED');
            $query->highPriority && $parts[] = new Expr('HIGH_PRIORITY');
            $query->ignore && $parts[] = new Expr('IGNORE');
        }

        $parts[] = new Expr('INTO');
        $parts[] = $this->escapeIdentifierDotted($query->into);
        if ($query->alias !== null) {
            $parts[] = 'AS ' . $this->escapeTableAlias($query->alias);
        }

        if ($query->columnNames) {
            $parts[] = new Expr(
                '(' . implode(', ', array_map($this->escapeIdentifier(...), $query->columnNames)) . ')',
            );
        }

        $parts[] = $this->buildInsertSource($query);

        if ($query instanceof PartitionInterface) {
            $parts[] = $this->buildPartition($query);
        }

        $expr = HExpr::mergeExpressionParts($parts, $this, ' ');

        return new BuiltQuery(
            $expr->getExpression($this),
            $expr->getParams(),
        );
    }
    // endregion METHOD_buildInsertQuery

    // region METHOD_buildSelectClause [DOMAIN(8): Grammar; TECH(8): Select]
    /**
     * @purpose Build MySQL-specific SELECT clause with HIGH_PRIORITY, STRAIGHT_JOIN, SQL_* hints.
     */
    protected function buildSelectClause(SelectQueryInterface $query): ExprInterface
    {
        if ($query instanceof MySqlSelectQuery) {
            $parts = [
                'SELECT',
                $query->highPriority ? 'HIGH_PRIORITY' : null,
                $query->straightJoin ? 'STRAIGHT_JOIN' : null,
                $query->sqlSmallResult ? 'SQL_SMALL_RESULT' : null,
                $query->sqlBigResult ? 'SQL_BIG_RESULT' : null,
                $query->sqlBufferResult ? 'SQL_BUFFER_RESULT' : null,
                $query->sqlNoCache ? 'SQL_NO_CACHE' : null,
                $query->sqlCalcFoundRows ? 'SQL_CALC_FOUND_ROWS' : null,
                $this->buildDistinctClause($query),
                $this->buildSelectColumns($query->selectColumns),
            ];

            return HExpr::mergeExpressionParts($parts, $this, ' ');
        } else {
            return parent::buildSelectClause($query);
        }
    }
    // endregion METHOD_buildSelectClause

    // region METHOD_buildLimitClause [DOMAIN(8): Grammar; TECH(8): Limit]
    /**
     * @purpose Build MySQL-specific LIMIT clause using offset, count syntax.
     */
    protected function buildLimitClause(LimitInterface $query): ?ExprInterface
    {
        if ($query->offset || $query->limit !== null) {
            $limit = $query->limit ?? '18446744073709551615';
            $offset = $query->offset;

            return new Expr("LIMIT $offset, $limit");
        }
        return null;
    }
    // endregion METHOD_buildLimitClause

    // region METHOD_buildLockClause [DOMAIN(8): Grammar; TECH(8): Lock]
    /**
     * @purpose Build MySQL FOR UPDATE / FOR SHARE lock clause.
     */
    protected function buildLockClause(SelectQueryInterface $query): ?ExprInterface
    {
        if ($query instanceof MySqlSelectQuery && $query->lockModes) {
            return new Expr(
                implode(
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

    // region METHOD_buildPartition [DOMAIN(8): Grammar; TECH(8): Partition]
    /**
     * @purpose Build MySQL PARTITION clause for partition pruning.
     */
    protected function buildPartition(PartitionInterface $query): ?ExprInterface
    {
        if ($query->partitions) {
            return new Expr(
                'PARTITION ('
                . implode(
                    ', ',
                    array_map(
                        $this->escapeIdentifier(...),
                        $query->partitions,
                    ),
                )
                . ')',
            );
        }
        return null;
    }
    // endregion METHOD_buildPartition
}
// endregion CLASS_MySqlGrammar
