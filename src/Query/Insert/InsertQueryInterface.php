<?php

namespace AndrewGos\QueryBuilder\Query\Insert;

use AndrewGos\QueryBuilder\Query\Interface\WithInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

interface InsertQueryInterface extends WithInterface
{
    public string $into {
        get;
    }
    public ?string $alias {
        get;
    }
    /**
     * @var string[] $columnNames
     */
    public array $columnNames {
        get;
    }
    /**
     * Source for insertion. NULL means "DEFAULT VALUES"
     *
     * @var SelectQueryInterface|ValuesQueryInterface|null $source
     */
    public SelectQueryInterface|ValuesQueryInterface|null $source {
        get;
    }

    /**
     * @param string $table
     * @param string[] $columnNames
     * @param string|null $alias
     *
     * @return $this
     */
    public function into(string $table, array $columnNames = [], ?string $alias = null): static;

    /**
     * Sets source for insertion. NULL means "DEFAULT VALUES"
     *
     * @param SelectQueryInterface|ValuesQueryInterface|null $source
     *
     * @return $this
     */
    public function source(SelectQueryInterface|ValuesQueryInterface|null $source): static;
}
