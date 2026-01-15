<?php

namespace AndrewGos\QueryBuilder\Query\Interface;

use AndrewGos\QueryBuilder\Enum\SetOperationEnum;
use AndrewGos\QueryBuilder\Expr\SetOperation\SetOperation;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

/**
 * This interface provides methods for working with UNION, INTERSECT, EXCEPT clauses
 */
interface OperationsInterface
{
    /**
     * @property SetOperation[]
     */
    public array $operations {
        get;
    }

    /**
     * @param SetOperationEnum $operation
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function operateWith(SetOperationEnum $operation, SelectQueryInterface ...$queries): static;

    /**
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function unionAll(SelectQueryInterface ...$queries): static;

    /**
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function intersectAll(SelectQueryInterface ...$queries): static;

    /**
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function exceptAll(SelectQueryInterface ...$queries): static;

    /**
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function unionDistinct(SelectQueryInterface ...$queries): static;

    /**
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function intersectDistinct(SelectQueryInterface ...$queries): static;

    /**
     * @param SelectQueryInterface ...$queries
     *
     * @return static
     */
    public function exceptDistinct(SelectQueryInterface ...$queries): static;
}
