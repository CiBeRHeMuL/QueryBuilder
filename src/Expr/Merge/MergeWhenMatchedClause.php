<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Merge;

use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(8): WhenMatchedClause; TECH(7): Clause]
/**
 * @moduleContract
 * @purpose Wrap a WHEN MATCHED action with optional AND conditions. Provides static factories ::update() and ::delete().
 *         Renders itself via getSql().
 * @scope MERGE clause value object.
 * @input MergeWhenMatchedActionInterface $action, array $and
 * @output Immutable clause DTO for MERGE query.
 * @invariants
 * - $and conditions are passed verbatim to AndExpr.
 * @modulemap
 * MergeWhenMatchedClause => WHEN MATCHED clause wrapper
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MergeWhenMatchedClause, WHEN MATCHED, MERGE, clause, static factory
// STRUCTURE: ▶ ┌$action, $and┐ + ::update(set, and) + ::delete(and) + getSql → ∑ [MergeWhenMatchedClause]

// region CLASS_MergeWhenMatchedClause [DOMAIN(8): Merge; CONCEPT(8): WhenMatchedClause; TECH(7): Clause]
/**
 * @template TSetValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null
 * @template TSet of array<int, SetClause>|array<string, TSetValue>
 * @template TConditionValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|null
 * @template TCondition of TConditionValue|array<TCondition>
 * @template TConditions of array<string, TCondition>|array<int, bool|ExprInterface>
 *
 * @purpose Final readonly DTO for WHEN MATCHED clause. Use static factories for concise construction.
 *         Renders as "WHEN MATCHED [AND cond] THEN action" via getSql().
 */
readonly class MergeWhenMatchedClause
{
    /**
     * @param MergeWhenMatchedActionInterface $action UPDATE/DELETE/DO NOTHING action for the clause
     * @param TConditions                     $and    optional AND conditions (passed to grammar's buildConditions)
     */
    public function __construct(
        protected(set) MergeWhenMatchedActionInterface $action,
        protected(set) array $and = [],
    ) {}

    // region METHOD_getSql [DOMAIN(8): Merge; CONCEPT(8): Rendering; TECH(8): SQL]
    /**
     * @purpose Render this clause as "WHEN MATCHED [AND cond] THEN action".
     * @complexity 4
     * STRUCTURE: ┌'WHEN MATCHED', ┌AND cond?┐, 'THEN', action.getSql┐ → ● merge → ∑ Expr
     *
     * @param GrammarInterface $grammar the grammar used for SQL rendering and identifier escaping
     *
     * @return ExprInterface
     */
    public function getSql(GrammarInterface $grammar): ExprInterface
    {
        $parts = ['WHEN MATCHED'];
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
     * @purpose Create a WHEN MATCHED THEN UPDATE SET clause.
     * @complexity 2
     *
     * @param TSet        $set short syntax: array<string, TSetValue> (column => value), or pre-built array<int, SetClause>
     * @param TConditions $and optional AND conditions
     *
     * @return self
     */
    public static function update(array $set, array $and = []): self
    {
        return new self(new MergeActionUpdate($set), $and);
    }

    /**
     * @purpose Create a WHEN MATCHED THEN DELETE clause.
     * @complexity 1
     *
     * @param TConditions $and optional AND conditions
     *
     * @return self
     */
    public static function delete(array $and = []): self
    {
        return new self(new MergeActionDelete(), $and);
    }
}
// endregion CLASS_MergeWhenMatchedClause
