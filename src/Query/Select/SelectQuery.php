<?php

namespace AndrewGos\QueryBuilder\Query\Select;

use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Enum\LimitBoundTypeEnum;
use AndrewGos\QueryBuilder\Enum\SetOperationEnum;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Lock\LockModeInterface;
use AndrewGos\QueryBuilder\Expr\OrExpr;
use AndrewGos\QueryBuilder\Expr\SetOperation\SetOperation;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Expr\Window\Window;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Expr\Join\JoinTable;
use AndrewGos\QueryBuilder\Query\Trait\FromTrait;
use AndrewGos\QueryBuilder\Query\Trait\JoinTrait;
use AndrewGos\QueryBuilder\Query\Trait\LimitTrait;
use AndrewGos\QueryBuilder\Query\Trait\OperationsTrait;
use AndrewGos\QueryBuilder\Query\Trait\OrderByTrait;
use AndrewGos\QueryBuilder\Query\Trait\WhereTrait;
use AndrewGos\QueryBuilder\Query\Trait\WithTrait;

class SelectQuery implements SelectQueryInterface
{
    use WithTrait;
    use FromTrait;
    use WhereTrait;
    use JoinTrait;
    use OperationsTrait;
    use OrderByTrait;
    use LimitTrait;

    /**
     * @inheritDoc
     */
    protected(set) array $selectColumns = [];
    protected(set) bool $distinct = false;
    /**
     * @inheritDoc
     */
    protected(set) array $groupBy = [];
    protected(set) bool $groupDistinct = false;
    /**
     * @inheritDoc
     */
    protected(set) array $having = [];
    /**
     * @inheritDoc
     */
    protected(set) array $windows = [];
    protected(set) ?LockModeInterface $lockMode = null;

    /**
     * @inheritDoc
     */
    public function select(array $columns): static
    {
        $this->selectColumns = $columns;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addSelect(array $columns): static
    {
        $this->selectColumns = array_merge(
            $this->selectColumns,
            $columns,
        );
        return $this;
    }

    public function distinct(bool $distinct = true): static
    {
        $this->distinct = $distinct;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function groupBy(array $columns, bool $distinct = false): static
    {
        $this->groupDistinct = $distinct;
        $this->groupBy = $columns;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addGroupBy(array $columns, bool $distinct = false): static
    {
        $this->groupDistinct = $distinct;
        $this->groupBy = array_merge($this->groupBy, $columns);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function having(array|ExprInterface $conditions): static
    {
        $this->having = $conditions instanceof ExprInterface ? [$conditions] : $conditions;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function andHaving(array|ExprInterface $conditions): static
    {
        $this->having = array_merge(
            $this->having,
            $conditions instanceof ExprInterface ? [$conditions] : $conditions,
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orHaving(array|ExprInterface $conditions): static
    {
        $this->having = [
            new OrExpr([
                new AndExpr($this->having),
                new AndExpr($conditions instanceof ExprInterface ? [$conditions] : $conditions),
            ]),
        ];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function window(string $name, Window $windowDefinition): static
    {
        $this->windows[$name] = $windowDefinition;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lock(?LockModeInterface $lockMode): static
    {
        $this->lockMode = $lockMode;

        return $this;
    }

    public function isReturnable(): bool
    {
        return true;
    }
}
