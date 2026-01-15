<?php

namespace AndrewGos\QueryBuilder\Query\Values;

use AndrewGos\QueryBuilder\Enum\LimitBoundTypeEnum;
use AndrewGos\QueryBuilder\Enum\SetOperationEnum;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Trait\LimitTrait;
use AndrewGos\QueryBuilder\Query\Trait\OperationsTrait;
use AndrewGos\QueryBuilder\Query\Trait\OrderByTrait;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

class ValuesQuery implements ValuesQueryInterface
{
    use OperationsTrait;
    use OrderByTrait;
    use LimitTrait;

    protected(set) array $values;

    /**
     * @inheritDoc
     */
    public function values(array $values): static
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addValues(array $values): static
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    public function isReturnable(): bool
    {
        return true;
    }
}
