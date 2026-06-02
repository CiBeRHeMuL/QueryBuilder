<?php

namespace AndrewGos\QueryBuilder\Query\Select\MySql;

use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Query\Interface\MySql\PartitionInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Trait\MySql\PartitionTrait;

// region MODULE_CONTRACT [DOMAIN(8): Select; CONCEPT(8): MySqlSelectQuery; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose MySQL-specific SELECT query with HIGH_PRIORITY, STRAIGHT_JOIN, SQL_* hints, PARTITION, and lock mode support.
 * @scope MySQL SELECT query building with MySQL-specific query hints.
 * @input Columns, tables, conditions, partitions, lock modes
 * @output MySqlSelectQuery instance with MySQL-specific SELECT capabilities
 * @invariants
 * - Implements PartitionInterface for MySQL partition pruning
 * @modulemap
 * MySqlSelectQuery => MySQL SELECT query with MySQL-specific hints and PARTITION
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MySQL, SELECT, HIGH_PRIORITY, STRAIGHT_JOIN, SQL_CALC_FOUND_ROWS, PARTITION, lock, dialect

// region CLASS_MySqlSelectQuery [DOMAIN(8): Select; CONCEPT(8): MySqlSelectQuery; TECH(8): Dialect]
/**
 * @purpose MySQL SELECT query extending SelectQuery with HIGH_PRIORITY, STRAIGHT_JOIN, SQL_* hints, PARTITION, and lock mode support.
 */
class MySqlSelectQuery extends SelectQuery implements PartitionInterface
{
    use PartitionTrait;

    protected(set) bool $highPriority = false;
    protected(set) bool $straightJoin = false;
    protected(set) bool $sqlSmallResult = false;
    protected(set) bool $sqlBigResult = false;
    protected(set) bool $sqlBufferResult = false;
    protected(set) bool $sqlNoCache = false;
    protected(set) bool $sqlCalcFoundRows = false;
    /**
     * @var LockModeInterface[] $lockModes
     */
    protected(set) array $lockModes = [];
    protected(set) ?LockModeInterface $lockMode = null {
        set {
            if ($value !== null) {
                $this->lockModes = [$value];
            } else {
                $this->lockModes = [];
            }
        }
        get => array_first($this->lockModes);
    }

    // region METHOD_highPriority [DOMAIN(8): Select; TECH(8): MySQLHints]
    /**
     * @purpose Set MySQL HIGH_PRIORITY modifier.
     */
    public function highPriority(bool $highPriority = true): static
    {
        $this->highPriority = $highPriority;

        return $this;
    }
    // endregion METHOD_highPriority

    // region METHOD_straightJoin [DOMAIN(8): Select; TECH(8): MySQLHints]
    /**
     * @purpose Set MySQL STRAIGHT_JOIN modifier.
     */
    public function straightJoin(bool $straightJoin = true): static
    {
        $this->straightJoin = $straightJoin;

        return $this;
    }
    // endregion METHOD_straightJoin

    // region METHOD_sqlSmallResult [DOMAIN(8): Select; TECH(8): MySQLHints]
    /**
     * @purpose Set MySQL SQL_SMALL_RESULT modifier.
     */
    public function sqlSmallResult(bool $sqlSmallResult = true): static
    {
        $this->sqlSmallResult = $sqlSmallResult;

        return $this;
    }
    // endregion METHOD_sqlSmallResult

    // region METHOD_sqlBigResult [DOMAIN(8): Select; TECH(8): MySQLHints]
    /**
     * @purpose Set MySQL SQL_BIG_RESULT modifier.
     */
    public function sqlBigResult(bool $sqlBigResult = true): static
    {
        $this->sqlBigResult = $sqlBigResult;

        return $this;
    }
    // endregion METHOD_sqlBigResult

    // region METHOD_sqlBufferResult [DOMAIN(8): Select; TECH(8): MySQLHints]
    /**
     * @purpose Set MySQL SQL_BUFFER_RESULT modifier.
     */
    public function sqlBufferResult(bool $sqlBufferResult = true): static
    {
        $this->sqlBufferResult = $sqlBufferResult;

        return $this;
    }
    // endregion METHOD_sqlBufferResult

    // region METHOD_sqlNoCache [DOMAIN(8): Select; TECH(8): MySQLHints]
    /**
     * @purpose Set MySQL SQL_NO_CACHE modifier.
     */
    public function sqlNoCache(bool $sqlNoCache = true): static
    {
        $this->sqlNoCache = $sqlNoCache;

        return $this;
    }
    // endregion METHOD_sqlNoCache

    // region METHOD_sqlCalcFoundRows [DOMAIN(8): Select; TECH(8): MySQLHints]
    /**
     * @purpose Set MySQL SQL_CALC_FOUND_ROWS modifier.
     */
    public function sqlCalcFoundRows(bool $sqlCalcFoundRows = true): static
    {
        $this->sqlCalcFoundRows = $sqlCalcFoundRows;

        return $this;
    }
    // endregion METHOD_sqlCalcFoundRows

    // region METHOD_addLock [DOMAIN(8): Select; TECH(8): Lock]
    /**
     * @purpose Add a lock mode (FOR UPDATE / FOR SHARE) to the SELECT query.
     */
    public function addLock(LockModeInterface $lockMode): static
    {
        $this->lockModes[] = $lockMode;

        return $this;
    }
    // endregion METHOD_addLock
}
// endregion CLASS_MySqlSelectQuery
