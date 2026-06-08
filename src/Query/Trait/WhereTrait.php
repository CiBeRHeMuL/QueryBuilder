<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\OrExpr;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Implement WhereInterface for WHERE clause support via reusable trait.
 * @scope Handles where(), andWhere(), orWhere() with AND/OR condition composability.
 * @input Condition arrays or ExprInterface.
 * @output Normalized WHERE clause state via WhereInterface contract.
 * @modulemap
 * TRAIT WhereTrait => WhereInterface implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: WHERE, trait, SQL, conditions, AND, OR, filtering
// STRUCTURE: ▶ where(conditions) | andWhere(merge) | orWhere(OrExpr[AndExpr(existing) + AndExpr(new)]) → ∑ [WhereTrait methods]

// region TRAIT_WhereTrait [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * This trait provides functionality of WhereInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\WhereInterface
 * @purpose Implement WhereInterface for queries requiring WHERE clause support.
 */
trait WhereTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $where = [];

    // region METHOD_where [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Set WHERE conditions, replacing any existing ones.
     * @io array|ExprInterface $conditions -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function where(array|ExprInterface $conditions): static
    {
        $this->where = $conditions instanceof ExprInterface ? [$conditions] : $conditions;

        return $this;
    }
    // endregion METHOD_where

    // region METHOD_andWhere [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Append conditions with AND logic to existing WHERE.
     * @io array|ExprInterface $conditions -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function andWhere(array|ExprInterface $conditions): static
    {
        $this->where = [
            new AndExpr($this->where),
            new AndExpr($conditions instanceof ExprInterface ? [$conditions] : $conditions),
        ];

        return $this;
    }
    // endregion METHOD_andWhere

    // region METHOD_orWhere [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Append conditions with OR logic, wrapping existing AND group.
     * @io array|ExprInterface $conditions -> static
     * @complexity 3
     *
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
    // endregion METHOD_orWhere
}
// endregion TRAIT_WhereTrait
