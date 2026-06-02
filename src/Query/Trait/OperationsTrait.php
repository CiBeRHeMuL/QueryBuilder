<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Enum\SetOperationEnum;
use AndrewGos\QueryBuilder\Expr\SetOperation\SetOperation;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Implement OperationsInterface for UNION, INTERSECT, EXCEPT set operations via reusable trait.
 * @scope Delegates all specific operation methods to operateWith().
 * @input Operation type and one or more SelectQueryInterface queries.
 * @output Normalized set operations state via OperationsInterface contract.
 * @modulemap
 * TRAIT OperationsTrait => OperationsInterface implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: UNION, INTERSECT, EXCEPT, set operations, trait, SQL

// region TRAIT_OperationsTrait [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * This trait provides functionality of OperationsInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\OperationsInterface
 * @purpose Implement OperationsInterface for queries requiring set operation support.
 */
trait OperationsTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $operations = [];

    // region METHOD_operateWith [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Apply a set operation, creating SetOperation objects for each query.
     * @io SetOperationEnum $operation, SelectQueryInterface ...$queries -> static
     * @complexity 3
     *
     * @inheritDoc
     */
    public function operateWith(SetOperationEnum $operation, SelectQueryInterface ...$queries): static
    {
        foreach ($queries as $query) {
            $this->operations[] = new SetOperation($operation, $query);
        }

        return $this;
    }
    // endregion METHOD_operateWith

    // region METHOD_unionAll [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Apply UNION ALL via operateWith().
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function unionAll(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::UnionAll,
            ...$queries,
        );
    }
    // endregion METHOD_unionAll

    // region METHOD_intersectAll [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Apply INTERSECT ALL via operateWith().
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function intersectAll(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::IntersectAll,
            ...$queries,
        );
    }
    // endregion METHOD_intersectAll

    // region METHOD_exceptAll [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Apply EXCEPT ALL via operateWith().
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function exceptAll(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::ExceptAll,
            ...$queries,
        );
    }
    // endregion METHOD_exceptAll

    // region METHOD_unionDistinct [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Apply UNION DISTINCT via operateWith().
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function unionDistinct(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::UnionDistinct,
            ...$queries,
        );
    }
    // endregion METHOD_unionDistinct

    // region METHOD_intersectDistinct [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Apply INTERSECT DISTINCT via operateWith().
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function intersectDistinct(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::IntersectDistinct,
            ...$queries,
        );
    }
    // endregion METHOD_intersectDistinct

    // region METHOD_exceptDistinct [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Apply EXCEPT DISTINCT via operateWith().
     * @io SelectQueryInterface ...$queries -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function exceptDistinct(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::ExceptDistinct,
            ...$queries,
        );
    }
    // endregion METHOD_exceptDistinct
}
// endregion TRAIT_OperationsTrait
