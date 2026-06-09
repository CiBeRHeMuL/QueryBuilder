<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Merge;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(7): ActionUpdate; TECH(6): ValueObject]
/**
 * @moduleContract
 * @purpose Represents the UPDATE SET action for WHEN MATCHED and WHEN NOT MATCHED BY SOURCE clauses. Normalizes short syntax (column => value) into SetClause[].
 *         In MERGE context, string values are treated as column references (e.g., "s"."name"), not bound params.
 * @scope MERGE action value object.
 * @input array $set (short syntax or SetClause[]), GrammarInterface
 * @output UPDATE SET SQL fragment and params.
 * @invariants
 * - String values in SET are treated as column identifiers, not bound params.
 * @modulemap
 * MergeActionUpdate => MERGE UPDATE SET action
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MergeActionUpdate, UPDATE SET, MERGE, action, normalization, column reference
// STRUCTURE: ▶ normalizeSet ┌string-keyed array → SetClause[]┐ + getSql → build set with string-as-identifier → ∑ string + params

// region CLASS_MergeActionUpdate [DOMAIN(8): Merge; CONCEPT(7): ActionUpdate; TECH(6): ValueObject]
/**
 * @template TSetValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null
 * @template TSet of array<int, SetClause>|array<string, TSetValue>
 *
 * @purpose UPDATE SET action for MERGE. Accepts same short syntax as UpdateQuery::set(). String values are treated as column references.
 */
class MergeActionUpdate implements MergeWhenMatchedActionInterface
{
    /** @var SetClause[] */
    protected(set) array $normalizedSet;
    private string $cachedSql = '';
    private array $cachedParams = [];

    /**
     * @param TSet $set short syntax: array<string, TSetValue> (column => value), or pre-built array<int, SetClause>
     */
    public function __construct(array $set)
    {
        $normalized = [];
        foreach ($set as $key => $value) {
            if ($value instanceof SetClause) {
                $normalized[] = $value;
            } else {
                $normalized[] = new SetClause((string) $key, $value);
            }
        }
        $this->normalizedSet = $normalized;
    }

    // region METHOD_getSql [DOMAIN(8): Merge; CONCEPT(7): ActionUpdate; TECH(8): Rendering]
    /**
     * @purpose Render the UPDATE SET SQL fragment. String values are treated as column identifiers, not bound params.
     * @complexity 5
     * STRUCTURE: ┌'SET', ○ foreach SetClause → escapeIdentifier(target) + ' = ' + ◇ string → escapeIdentifierDotted | else → ValueBuilder.build┐ → ● implode(', ') → ∑ string + cache params
     *
     * @param GrammarInterface $grammar the grammar used for SQL rendering and identifier escaping
     *
     * @return string
     */
    public function getSql(GrammarInterface $grammar): string
    {
        $parts = [];
        $params = [];
        $vb = new ValueBuilder();

        foreach ($this->normalizedSet as $clause) {
            $target = is_string($clause->target)
                ? $grammar->escapeIdentifier($clause->target)
                : throw new QueryBuilderException('Multi-column SET targets are not supported in MERGE context');

            if ($clause->value instanceof ExprInterface) {
                $valueSql = $clause->value->getExpression($grammar);
                $params = HExpr::mergeParams($params, $clause->value->getParams());
            } elseif ($clause->value instanceof SelectQueryInterface || $clause->value instanceof ValuesQueryInterface) {
                $bq = $clause->value instanceof SelectQueryInterface
                    ? $grammar->buildSelectQuery($clause->value)
                    : $grammar->buildValuesQuery($clause->value);
                $valueSql = '(' . $bq->sql . ')';
                $params = HExpr::mergeParams($params, $bq->params);
            } elseif (is_string($clause->value)) {
                $valueSql = $grammar->escapeIdentifierDotted($clause->value);
            } else {
                $expr = $vb->build($clause->value, $grammar);
                $valueSql = $expr->getExpression($grammar);
                $params = HExpr::mergeParams($params, $expr->getParams());
            }

            $parts[] = $target . ' = ' . $valueSql;
        }

        $this->cachedSql = 'UPDATE SET ' . implode(', ', $parts);
        $this->cachedParams = $params;

        return $this->cachedSql;
    }
    // endregion METHOD_getSql

    // region METHOD_getParams [DOMAIN(8): Merge; CONCEPT(7): ActionUpdate; TECH(6): Params]
    /**
     * @purpose Return the bound parameters from the SET expressions. Must be called after getSql().
     * @complexity 1
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->cachedParams;
    }
    // endregion METHOD_getParams
}
// endregion CLASS_MergeActionUpdate
