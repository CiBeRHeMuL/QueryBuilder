<?php

namespace AndrewGos\QueryBuilder\Query\Insert;

use AndrewGos\QueryBuilder\Query\Insert\InsertQueryInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Trait\WithTrait;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

class InsertQuery implements InsertQueryInterface
{
    use WithTrait;

    protected(set) string $into;
    protected(set) ?string $alias = null;
    /**
     * @inheritDoc
     */
    protected(set) array $columnNames = [];
    /**
     * @inheritDoc
     */
    protected(set) SelectQueryInterface|ValuesQueryInterface|null $source = null;

    /**
     * @inheritDoc
     */
    public function into(string $table, array $columnNames = [], ?string $alias = null): static
    {
        $this->into = $table;
        $this->columnNames = $columnNames;
        $this->alias = $alias;

        return $this;
    }

    public function source(ValuesQueryInterface|SelectQueryInterface|null $source): static
    {
        $this->source = $source;

        return $this;
    }
}
