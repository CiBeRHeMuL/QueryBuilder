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
use AndrewGos\QueryBuilder\Query\Interface\LimitInterface;
use AndrewGos\QueryBuilder\Query\Interface\MySql\PartitionInterface;
use AndrewGos\QueryBuilder\Query\Select\MySql\MySqlSelectQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

class MySqlGrammar extends AbstractGrammar
{
    public function escapeIdentifier(string $identifier): string
    {
        if ($identifier === '*') {
            return $identifier;
        }
        $identifier = trim($identifier, " \n\r\t\v\0`");
        return '`' . strtr($identifier, ['`' => '``']) . '`';
    }

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

    protected function buildLimitClause(LimitInterface $query): ?ExprInterface
    {
        if ($query->offset || $query->limit !== null) {
            $limit = $query->limit ?? '18446744073709551615';
            $offset = $query->offset;

            return new Expr("LIMIT $offset, $limit");
        }
        return null;
    }

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
}
