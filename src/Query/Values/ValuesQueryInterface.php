<?php

namespace AndrewGos\QueryBuilder\Query\Values;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Query\Interface\LimitInterface;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Interface\OperationsInterface;
use AndrewGos\QueryBuilder\Query\Interface\OrderByInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Values; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Define the contract for VALUES SQL queries with ORDER BY, LIMIT, set operations, and returnable marker.
 * @scope Interface extending OperationsInterface, OrderByInterface, LimitInterface, MaybeReturnableQueryInterface.
 * @input Nested value arrays.
 * @output Contract for VALUES query DTO.
 * @modulemap
 * INTERFACE ValuesQueryInterface => VALUES query contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: VALUES, SQL, query, value lists, inline values, interface

// region INTERFACE_ValuesQueryInterface [DOMAIN(8): Query; CONCEPT(9): Values; TECH(8): SQL]
/**
 * @template TSimpleValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TValues of TSimpleValue|TValues[]
 * @purpose Define the contract for VALUES SQL queries.
 */
interface ValuesQueryInterface extends OperationsInterface, OrderByInterface, LimitInterface, MaybeReturnableQueryInterface
{
    /**
     * @var TValues
     */
    public array $values {
        get;
    }

    // region METHOD_values [DOMAIN(8): Query; CONCEPT(9): Values; TECH(8): SQL]
    /**
     * @purpose Set the value rows, replacing any existing ones.
     * @io TValues $values -> static
     * @complexity 2
     *
     * @param TValues $values
     *
     * @return static
     */
    public function values(array $values): static;
    // endregion METHOD_values

    // region METHOD_addValues [DOMAIN(8): Query; CONCEPT(9): Values; TECH(8): SQL]
    /**
     * @purpose Append additional value rows to the existing list.
     * @io TValues $values -> static
     * @complexity 2
     *
     * @param TValues $values
     *
     * @return static
     */
    public function addValues(array $values): static;
    // endregion METHOD_addValues
}
// endregion INTERFACE_ValuesQueryInterface
