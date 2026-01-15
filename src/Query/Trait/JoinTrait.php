<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Join\JoinTable;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

/**
 * This trait provides functionality of JoinInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\JoinInterface
 */
trait JoinTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $joinTables = [];

    /**
     * @inheritDoc
     */
    public function join(
        JoinTypeEnum $type,
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        $joinTable = new JoinTable(
            $type,
            HExpr::normalizeTable($table),
            $conditions,
            false,
        );

        if ($alias !== null) {
            $this->joinTables[$alias] = $joinTable;
        } else {
            $this->joinTables[] = $joinTable;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function innerJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        return $this->join(
            JoinTypeEnum::InnerJoin,
            $table,
            $conditions,
            $alias,
        );
    }

    /**
     * @inheritDoc
     */
    public function leftJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        return $this->join(
            JoinTypeEnum::LeftOuterJoin,
            $table,
            $conditions,
            $alias,
        );
    }

    /**
     * @inheritDoc
     */
    public function rightJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        return $this->join(
            JoinTypeEnum::RightOuterJoin,
            $table,
            $conditions,
            $alias,
        );
    }

    /**
     * @inheritDoc
     */
    public function crossJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        return $this->join(
            JoinTypeEnum::CrossJoin,
            $table,
            $conditions,
            $alias,
        );
    }

    /**
     * @inheritDoc
     */
    public function fullJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        return $this->join(
            JoinTypeEnum::FullOuterJoin,
            $table,
            $conditions,
            $alias,
        );
    }

    /**
     * @inheritDoc
     */
    public function naturalJoin(
        JoinTypeEnum $type,
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        if ($type === JoinTypeEnum::CrossJoin) {
            throw QueryBuilderException::invalidNaturalJoinType($type);
        }

        $joinTable = new JoinTable(
            $type,
            HExpr::normalizeTable($table),
            naturalJoin: true,
        );

        if ($alias !== null) {
            $this->joinTables[$alias] = $joinTable;
        } else {
            $this->joinTables[] = $joinTable;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function naturalInnerJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        return $this->naturalJoin(
            JoinTypeEnum::InnerJoin,
            $table,
            $alias,
        );
    }

    /**
     * @inheritDoc
     */
    public function naturalLeftJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        return $this->naturalJoin(
            JoinTypeEnum::LeftOuterJoin,
            $table,
            $alias,
        );
    }

    /**
     * @inheritDoc
     */
    public function naturalRightJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        return $this->naturalJoin(
            JoinTypeEnum::RightOuterJoin,
            $table,
            $alias,
        );
    }

    /**
     * @inheritDoc
     */
    public function naturalFullJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        return $this->naturalJoin(
            JoinTypeEnum::FullOuterJoin,
            $table,
            $alias,
        );
    }
}
