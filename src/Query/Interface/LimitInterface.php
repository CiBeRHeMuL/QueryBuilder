<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Enum\LimitBoundTypeEnum;
use AndrewGos\QueryBuilder\Expr\ExprInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Pagination; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for SQL OFFSET, LIMIT, and FETCH clauses.
 * @scope Offset and limit configuration with bound type support.
 * @input Offset value, limit value, and bound type enum.
 * @output Contract for result set pagination.
 * @modulemap
 * INTERFACE LimitInterface => OFFSET/LIMIT/FETCH clause contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: LIMIT, OFFSET, FETCH, pagination, SQL, bound type
// STRUCTURE: ▶ ┌offset, limit, limitBoundType┐ + offset() + limit() → ∑ [LimitInterface contract]

// region INTERFACE_LimitInterface [DOMAIN(8): Query; CONCEPT(9): Pagination; TECH(8): SQL]
/**
 * This interface provides methods for working with OFFSET, LIMIT, FETCH clause.
 *
 * @purpose Define methods for working with OFFSET, LIMIT, FETCH clause.
 */
interface LimitInterface
{
    public int|ExprInterface $offset {
        get;
    }
    public int|ExprInterface|null $limit {
        get;
    }
    public LimitBoundTypeEnum $limitBoundType {
        get;
    }

    // region METHOD_offset [DOMAIN(8): Query; CONCEPT(9): Pagination; TECH(8): SQL]
    /**
     * @purpose Set the OFFSET value for the query, clamping to 0 for integer inputs.
     * @io int|ExprInterface $offset -> static
     * @complexity 2
     *
     * @param int|ExprInterface $offset
     *
     * @return static
     */
    public function offset(int|ExprInterface $offset): static;
    // endregion METHOD_offset

    // region METHOD_limit [DOMAIN(8): Query; CONCEPT(9): Pagination; TECH(8): SQL]
    /**
     * @purpose Set the LIMIT value with optional bound type (ONLY, WITH_TIES).
     * @io int|ExprInterface|null $limit, LimitBoundTypeEnum $boundType -> static
     * @complexity 2
     *
     * @param int|ExprInterface|null $limit
     * @param LimitBoundTypeEnum     $boundType
     *
     * @return static
     */
    public function limit(int|ExprInterface|null $limit, LimitBoundTypeEnum $boundType = LimitBoundTypeEnum::Only): static;
    // endregion METHOD_limit
}
// endregion INTERFACE_LimitInterface
