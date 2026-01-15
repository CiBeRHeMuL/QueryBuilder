<?php

namespace AndrewGos\QueryBuilder\Query\Select\MySql;

use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Query\Interface\MySql\PartitionInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Trait\MySql\PartitionTrait;

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
                $this->lockModes[] = $value;
            } else {
                $this->lockModes = [];
            }
        }
        get => array_first($this->lockModes);
    }

    public function highPriority(bool $highPriority = true): static
    {
        $this->highPriority = $highPriority;

        return $this;
    }

    public function straightJoin(bool $straightJoin = true): static
    {
        $this->straightJoin = $straightJoin;

        return $this;
    }

    public function sqlSmallResult(bool $sqlSmallResult = true): static
    {
        $this->sqlSmallResult = $sqlSmallResult;

        return $this;
    }

    public function sqlBigResult(bool $sqlBigResult = true): static
    {
        $this->sqlBigResult = $sqlBigResult;

        return $this;
    }

    public function sqlBufferResult(bool $sqlBufferResult = true): static
    {
        $this->sqlBufferResult = $sqlBufferResult;

        return $this;
    }

    public function sqlNoCache(bool $sqlNoCache = true): static
    {
        $this->sqlNoCache = $sqlNoCache;

        return $this;
    }

    public function sqlCalcFoundRows(bool $sqlCalcFoundRows = true): static
    {
        $this->sqlCalcFoundRows = $sqlCalcFoundRows;

        return $this;
    }

    public function addLock(LockModeInterface $lockMode): static
    {
        $this->lockModes[] = $lockMode;

        return $this;
    }
}
