<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\OrExpr;

/**
 * This trait provides functionality of WhereInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\WhereInterface
 */
trait WhereTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $where = [];

    /**
     * @inheritDoc
     */
    public function where(array|ExprInterface $conditions): static
    {
        $this->where = $conditions instanceof ExprInterface ? [$conditions] : $conditions;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function andWhere(array|ExprInterface $conditions): static
    {
        $this->where = array_merge(
            $this->where,
            $conditions instanceof ExprInterface ? [$conditions] : $conditions,
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orWhere(array|ExprInterface $conditions): static
    {
        $this->where = [
            new OrExpr([
                new AndExpr($this->where),
                new AndExpr($conditions instanceof ExprInterface ? [$conditions] : $conditions),
            ]),
        ];

        return $this;
    }
}
