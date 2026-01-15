<?php

namespace AndrewGos\QueryBuilder\Query\Values;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Query\Interface\LimitInterface;
use AndrewGos\QueryBuilder\Query\Interface\OperationsInterface;
use AndrewGos\QueryBuilder\Query\Interface\OrderByInterface;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use UnitEnum;

/**
 * @template TSimpleValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TValues of TSimpleValue|TValues[]
 */
interface ValuesQueryInterface extends OperationsInterface, OrderByInterface, LimitInterface, MaybeReturnableQueryInterface
{
    /**
     * @var TValues
     */
    public array $values {
        get;
    }

    /**
     * @param TValues $values
     *
     * @return static
     */
    public function values(array $values): static;

    /**
     * @param TValues $values
     *
     * @return static
     */
    public function addValues(array $values): static;
}
