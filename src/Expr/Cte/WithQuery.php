<?php

namespace AndrewGos\QueryBuilder\Expr\Cte;

use AndrewGos\QueryBuilder\Enum\Cte\SearchTypeEnum;
use AndrewGos\QueryBuilder\Expr\Literal;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;

class WithQuery
{
    public function __construct(
        protected(set) MaybeReturnableQueryInterface $query,
        protected(set) ?Search $search = null,
        protected(set) ?Cycle $cycle = null,
    ) {}

    /**
     * @param SearchTypeEnum $type
     * @param string[] $columns
     * @param string $searchSeqColumnName
     *
     * @return WithQuery
     */
    public function search(
        SearchTypeEnum $type,
        array $columns,
        string $searchSeqColumnName,
    ): static {
        $this->search = new Search($type, $columns, $searchSeqColumnName);

        return $this;
    }

    /**
     * @param string[] $columns
     * @param string $cycleMarkColumnName
     * @param string $cyclePathColumnName
     * @param bool|int|float|string|Literal|null $cycleMarkValue
     * @param bool|int|float|string|Literal|null $cycleMarkDefault
     *
     * @return WithQuery
     */
    public function cycle(
        array $columns,
        string $cycleMarkColumnName,
        string $cyclePathColumnName,
        bool|int|float|string|Literal|null $cycleMarkValue = true,
        bool|int|float|string|Literal|null $cycleMarkDefault = false,
    ): static {
        $cycleMarkValue = $cycleMarkValue instanceof Literal ? $cycleMarkValue : new Literal($cycleMarkValue);
        $cycleMarkDefault = $cycleMarkDefault instanceof Literal ? $cycleMarkDefault : new Literal($cycleMarkDefault);

        $this->cycle = new Cycle(
            $columns,
            $cycleMarkColumnName,
            $cyclePathColumnName,
            $cycleMarkValue,
            $cycleMarkDefault,
        );

        return $this;
    }
}
