<?php

namespace AndrewGos\QueryBuilder\Query\Select\PgSql;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @phpstan-template TExpression of TValue|array<TExpression>
 */
class PgSqlSelectQuery extends SelectQuery
{
    /**
     * @var TExpression[] $distinctOn
     */
    protected(set) array $distinctOn = [];
    protected(set) bool $distinct = false {
        get => $this->distinct || !empty($this->distinctOn);
        set {
            $this->distinct = $value;
            if ($this->distinct === false) {
                $this->distinctOn = [];
            }
        }
    }
    /**
     * @var LockModeInterface[] $lockModes
     */
    protected(set) array $lockModes = [];
    protected(set) ?LockModeInterface $lockMode = null {
        set {
            if ($value !== null) {
                $this->lockModes[] = $value;
            } else {
                $this->lockModes = [];
            }
        }
        get => array_first($this->lockModes);
    }

    /**
     * @param TExpression[] $columns
     *
     * @return PgSqlSelectQuery
     */
    public function distinctOn(array $columns): static
    {
        $this->distinctOn = $columns;

        return $this;
    }

    /**
     * @param TExpression[] $columns
     *
     * @return PgSqlSelectQuery
     */
    public function addDistinctOn(array $columns): static
    {
        $this->distinctOn = $columns;

        return $this;
    }

    public function addLock(LockModeInterface $lockMode): static
    {
        $this->lockModes[] = $lockMode;

        return $this;
    }
}
