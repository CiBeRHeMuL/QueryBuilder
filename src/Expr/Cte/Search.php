<?php

namespace AndrewGos\QueryBuilder\Expr\Cte;

use AndrewGos\QueryBuilder\Enum\Cte\SearchTypeEnum;

final readonly class Search
{
    /**
     * @param SearchTypeEnum $type
     * @param string[] $columns
     * @param string $searchSeqColumnName
     */
    public function __construct(
        private(set) SearchTypeEnum $type,
        private(set) array $columns,
        private(set) string $searchSeqColumnName,
    ) {}
}
