<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Merge;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;

// region MODULE_CONTRACT [DOMAIN(8): Merge; CONCEPT(7): ActionInsert; TECH(6): ValueObject]
/**
 * @moduleContract
 * @purpose Represents the INSERT action for WHEN NOT MATCHED (BY TARGET) clause. Renders as "INSERT (columns) VALUES (values)".
 *         Accepts a single associative array mapping column names to values.
 *         In MERGE context, string values are treated as column references (e.g., "s"."id"), not bound params.
 * @scope MERGE action value object.
 * @input array<string, mixed> $values, GrammarInterface
 * @output INSERT SQL fragment and params.
 * @invariants
 * - String values are treated as column identifiers, not bound params.
 * @modulemap
 * MergeActionInsert => MERGE INSERT action
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MergeActionInsert, INSERT, MERGE, action, column reference
// STRUCTURE: ▶ getSql → ┌'INSERT (...columns...) VALUES (...values...)┐ with string-as-identifier → ∑ string + params

// region CLASS_MergeActionInsert [DOMAIN(8): Merge; CONCEPT(7): ActionInsert; TECH(6): ValueObject]
/**
 * @purpose INSERT action for MERGE WHEN NOT MATCHED (BY TARGET). Accepts associative array column => value. String values are treated as column references.
 */
class MergeActionInsert implements MergeWhenNotMatchedActionInterface
{
    private string $cachedSql = '';
    private array $cachedParams = [];

    /** @param array<string, mixed> $values Associative array mapping column names to values */
    public function __construct(
        protected(set) array $values,
    ) {}

    // region METHOD_getSql [DOMAIN(8): Merge; CONCEPT(7): ActionInsert; TECH(8): Rendering]
    /**
     * @purpose Render as "INSERT (col1, col2) VALUES (val1, val2)". String values are treated as column identifiers.
     * @complexity 5
     * STRUCTURE: ┌'INSERT', columns(), 'VALUES', values() ┤string→escapeIdentifierDotted, Expr→raw, else→ValueBuilder├┐ → ● merge → ∑ string
     *
     * @param GrammarInterface $grammar the grammar used for SQL rendering and identifier escaping
     *
     * @return string
     */
    public function getSql(GrammarInterface $grammar): string
    {
        $vb = new ValueBuilder();
        $valueParts = [];
        $params = [];

        foreach ($this->values as $column => $value) {
            if (is_string($value)) {
                $valueParts[] = $grammar->escapeIdentifierDotted($value);
            } elseif ($value instanceof ExprInterface) {
                $valueParts[] = $value->getExpression($grammar);
                $params = HExpr::mergeParams($params, $value->getParams());
            } else {
                $expr = $vb->build($value, $grammar);
                $valueParts[] = $expr->getExpression($grammar);
                $params = HExpr::mergeParams($params, $expr->getParams());
            }
        }

        $escapedColumns = array_map($grammar->escapeIdentifier(...), array_keys($this->values));

        $this->cachedSql = sprintf(
            'INSERT (%s) VALUES (%s)',
            implode(', ', $escapedColumns),
            implode(', ', $valueParts),
        );
        $this->cachedParams = $params;

        return $this->cachedSql;
    }
    // endregion METHOD_getSql

    // region METHOD_getParams [DOMAIN(8): Merge; CONCEPT(7): ActionInsert; TECH(6): Params]
    /**
     * @purpose Return the bound parameters from the value expressions. Must be called after getSql().
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
// endregion CLASS_MergeActionInsert
