<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Merge;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenMatchedClause;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenNotMatchedBySourceClause;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenNotMatchedClause;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Query\Interface\WithInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(10): MergeQuery; TECH(8): ANSI_SQL]
/**
 * @moduleContract
 * @purpose Define the contract for ANSI SQL:2008 MERGE queries. Core operations: into (target), using (source), on (condition), whenMatched, whenNotMatched.
 * @scope Interface extending WithInterface for CTE support.
 * @input Target table, source, condition, WHEN clauses.
 * @output Contract for MERGE query DTO.
 * @invariants
 * - using() source is normalized: string → SelectTable, other types pass through.
 * @modulemap
 * INTERFACE MergeQueryInterface => ANSI MERGE query contract
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MergeQueryInterface, MERGE, ANSI, SQL:2008, query contract, upsert
// STRUCTURE: ▶ WithInterface + into(table, alias) + using(source, alias) + on(condition) + whenMatched() + whenNotMatched() → ∑ [MergeQueryInterface contract]

// region INTERFACE_MergeQueryInterface [DOMAIN(8): Merge; CONCEPT(10): MergeQuery; TECH(8): ANSI_SQL]
/**
 * @purpose Contract for ANSI MERGE (SQL:2008) queries. Compose with WithInterface for CTE support.
 */
interface MergeQueryInterface extends WithInterface
{
    public string $into {
        get;
    }
    public ?string $alias {
        get;
    }
    public SelectTable|SelectQueryInterface|ValuesQueryInterface|ExprInterface $using {
        get;
    }
    public ?string $usingAlias {
        get;
    }
    /** @var array<string, mixed> */
    public array $on {
        get;
    }
    /** @var MergeWhenMatchedClause[] */
    public array $whenMatchedClauses {
        get;
    }
    /** @var MergeWhenNotMatchedClause[] */
    public array $whenNotMatchedClauses {
        get;
    }
    /** @var MergeWhenNotMatchedBySourceClause[] */
    public array $whenNotMatchedBySourceClauses {
        get;
    }

    /**
     * @param string      $table target table name
     * @param string|null $alias optional alias for target table
     *
     * @return static
     */
    public function into(string $table, ?string $alias = null): static;

    /**
     * @param string|SelectTable|SelectQueryInterface|ValuesQueryInterface|ExprInterface $source source: table name (string → SelectTable), SelectTable, SELECT query, VALUES query, or raw expression
     * @param string|null                                                                $alias  optional alias for source
     *
     * @return static
     */
    public function using(
        string|SelectTable|SelectQueryInterface|ValuesQueryInterface|ExprInterface $source,
        ?string $alias = null,
    ): static;

    /**
     * @param array<string, mixed> $conditions Join conditions keyed by target column (e.g., ['t.id' => 's.id']).
     *
     * @return static
     */
    public function on(array $conditions): static;

    /**
     * @param MergeWhenMatchedClause ...$clauses One or more WHEN MATCHED THEN action clauses.
     *
     * @return static
     */
    public function whenMatched(MergeWhenMatchedClause ...$clauses): static;

    /**
     * @param MergeWhenNotMatchedClause ...$clauses One or more WHEN NOT MATCHED THEN action clauses.
     *
     * @return static
     */
    public function whenNotMatched(MergeWhenNotMatchedClause ...$clauses): static;

    /**
     * @param MergeWhenNotMatchedBySourceClause ...$clauses One or more WHEN NOT MATCHED BY SOURCE clauses.
     *
     * @return static
     */
    public function whenNotMatchedBySource(MergeWhenNotMatchedBySourceClause ...$clauses): static;
}
// endregion INTERFACE_MergeQueryInterface
