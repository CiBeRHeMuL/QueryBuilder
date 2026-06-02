<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Enum\LimitBoundTypeEnum;
use AndrewGos\QueryBuilder\Expr\ExprInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Implement LimitInterface for OFFSET, LIMIT, FETCH clause support via reusable trait.
 * @scope Manages offset/limit with non-negative clamping for integer values.
 * @input Offset value, limit value, bound type.
 * @output Normalized LIMIT clause state via LimitInterface contract.
 * @modulemap
 * TRAIT LimitTrait => LimitInterface implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: LIMIT, OFFSET, FETCH, trait, SQL, pagination, bound type

// region TRAIT_LimitTrait [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * This trait provides functionality of LimitInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\LimitInterface
 * @purpose Implement LimitInterface for queries requiring pagination support.
 */
trait LimitTrait
{
    protected(set) int|ExprInterface $offset = 0;
    protected(set) int|ExprInterface|null $limit = null;
    protected(set) LimitBoundTypeEnum $limitBoundType = LimitBoundTypeEnum::Only;

    // region METHOD_offset [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Set the OFFSET, clamping to 0 for integer inputs.
     * @io int|ExprInterface $offset -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function offset(int|ExprInterface $offset): static
    {
        $this->offset = is_int($offset) ? max(0, $offset) : $offset;

        return $this;
    }
    // endregion METHOD_offset

    // region METHOD_limit [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Set the LIMIT with optional bound type, clamping to 0 for integer inputs.
     * @io int|ExprInterface|null $limit, LimitBoundTypeEnum $boundType -> static
     * @complexity 2
     *
     * @inheritDoc
     */
    public function limit(int|ExprInterface|null $limit, LimitBoundTypeEnum $boundType = LimitBoundTypeEnum::Only): static
    {
        $this->limit = is_int($limit) ? max(0, $limit) : $limit;
        $this->limitBoundType = $boundType;

        return $this;
    }
    // endregion METHOD_limit
}
// endregion TRAIT_LimitTrait
