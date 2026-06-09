<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Merge;

use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(8): WhenNotMatchedClause; TECH(7): Clause]
/**
 * @moduleContract
 * @purpose Wrap a WHEN NOT MATCHED (BY TARGET) action with optional AND conditions. Provides static factory ::insert().
 *         Renders itself via getSql().
 * @scope MERGE clause value object.
 * @input MergeWhenNotMatchedActionInterface $action, array $and
 * @output Immutable clause DTO for MERGE query.
 * @invariants
 * - $and conditions are passed verbatim to AndExpr.
 * - Action must be MergeWhenNotMatchedActionInterface (INSERT or DO NOTHING).
 * @modulemap
 * MergeWhenNotMatchedClause => WHEN NOT MATCHED (BY TARGET) clause wrapper
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MergeWhenNotMatchedClause, WHEN NOT MATCHED, MERGE, clause, static factory
// STRUCTURE: ▶ ┌$action, $and┐ + ::insert(columns, values, and) + getSql → ∑ [MergeWhenNotMatchedClause]

// region CLASS_MergeWhenNotMatchedClause [DOMAIN(8): Merge; CONCEPT(8): WhenNotMatchedClause; TECH(7): Clause]
/**
 * @template TInsertValue of bool|int|float|string|UnitEnum|ExprInterface|null
 * @template TInsertValues of array<string, TInsertValue>
 * @template TConditionValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|null
 * @template TCondition of TConditionValue|array<TCondition>
 * @template TConditions of array<string, TCondition>|array<int, bool|ExprInterface>
 *
 * @purpose Final readonly DTO for WHEN NOT MATCHED (BY TARGET) clause. Use static factory for concise construction.
 *         Renders as "WHEN NOT MATCHED [AND cond] THEN action" via getSql().
 */
readonly class MergeWhenNotMatchedClause
{
    /**
     * @param MergeWhenNotMatchedActionInterface $action INSERT/DO NOTHING action for the clause
     * @param TConditions                        $and    optional AND conditions (passed to grammar's buildConditions)
     */
    public function __construct(
        protected(set) MergeWhenNotMatchedActionInterface $action,
        protected(set) array $and = [],
    ) {}

    // region METHOD_getSql [DOMAIN(8): Merge; CONCEPT(8): Rendering; TECH(8): SQL]
    /**
     * @purpose Render this clause as "WHEN NOT MATCHED [AND cond] THEN action".
     * @complexity 4
     * STRUCTURE: ┌'WHEN NOT MATCHED', ┌AND cond?┐, 'THEN', action.getSql┐ → ● merge → ∑ Expr
     *
     * @param GrammarInterface $grammar the grammar used for SQL rendering and identifier escaping
     *
     * @return ExprInterface
     */
    public function getSql(GrammarInterface $grammar): ExprInterface
    {
        $parts = ['WHEN NOT MATCHED'];
        $params = [];

        if ($this->and) {
            $andExpr = new AndExpr($this->and);
            $parts[] = 'AND ' . $andExpr->getExpression($grammar);
            $params = HExpr::mergeParams($params, $andExpr->getParams());
        }

        $parts[] = 'THEN';
        $parts[] = $this->action->getSql($grammar);
        $params = HExpr::mergeParams($params, $this->action->getParams());

        return new Expr(implode(' ', $parts), $params);
    }
    // endregion METHOD_getSql

    /**
     * @purpose Create a WHEN NOT MATCHED THEN INSERT (columns) VALUES (values) clause.
     * @complexity 2
     *
     * @param TInsertValues $values associative array mapping column names to values
     * @param TConditions   $and    optional AND conditions
     *
     * @return self
     */
    public static function insert(array $values, array $and = []): self
    {
        return new self(new MergeActionInsert($values), $and);
    }
}
// endregion CLASS_MergeWhenNotMatchedClause
