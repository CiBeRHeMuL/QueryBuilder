<?php

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Enum\SetOperationEnum;
use AndrewGos\QueryBuilder\Expr\SetOperation\SetOperation;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;

/**
 * This trait provides functionality of OperationsInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\OperationsInterface
 */
trait OperationsTrait
{
    /**
     * @inheritDoc
     */
    protected(set) array $operations = [];

    /**
     * @inheritDoc
     */
    public function operateWith(SetOperationEnum $operation, SelectQueryInterface ...$queries): static
    {
        foreach ($queries as $query) {
            $this->operations[] = new SetOperation($operation, $query);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unionAll(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::UnionAll,
            ...$queries,
        );
    }

    /**
     * @inheritDoc
     */
    public function intersectAll(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::IntersectAll,
            ...$queries,
        );
    }

    /**
     * @inheritDoc
     */
    public function exceptAll(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::ExceptAll,
            ...$queries,
        );
    }

    /**
     * @inheritDoc
     */
    public function unionDistinct(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::UnionDistinct,
            ...$queries,
        );
    }

    /**
     * @inheritDoc
     */
    public function intersectDistinct(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::IntersectDistinct,
            ...$queries,
        );
    }

    /**
     * @inheritDoc
     */
    public function exceptDistinct(SelectQueryInterface ...$queries): static
    {
        return $this->operateWith(
            SetOperationEnum::ExceptDistinct,
            ...$queries,
        );
    }
}
