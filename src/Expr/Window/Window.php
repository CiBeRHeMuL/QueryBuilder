<?php

namespace AndrewGos\QueryBuilder\Expr\Window;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Enum\Window\FrameBoundEnum;
use AndrewGos\QueryBuilder\Enum\Window\FrameExclusionEnum;
use AndrewGos\QueryBuilder\Enum\Window\FrameTypeEnum;
use AndrewGos\QueryBuilder\Expr\AbstractExpr;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Order\OrderColumn;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(9): Window; CONCEPT(9): WindowFunction; TECH(8): SQLAnalytics]
/**
 * @moduleContract
 * @purpose Provides a fluent builder for SQL window (OVER) clause definitions including partitioning, ordering, and frame specification.
 * @scope Full window definition: named window reference, PARTITION BY, ORDER BY, frame (RANGE/ROWS/GROUPS), and frame exclusion.
 * @input Partition columns, order columns, frame type/bounds/exclusion.
 * @output Rendered window definition SQL with bound parameters.
 * @modulemap
 * Window => Fluent window definition builder
 * @invariants
 * - Frame start/end offsets are null when bound is CurrentRow
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: window function, OVER, PARTITION BY, ORDER BY, frame, RANGE, ROWS, GROUPS, analytics, SQL
// STRUCTURE: ▶ Init ┌optional existingWindowName, partitionBy[], orderBy[], frame params┐ → ◇ has existing name? → ⊕ name → ◇ has partitions? → ○ buildPartitionBy → ⊕ sql+params → ◇ has order? → ○ buildOrderBy → ⊕ sql+params → ◇ has frame? → ○ buildFrame (◇ type BETWEEN ◇ offset/UNBOUNDED start ◇ AND ◇ offset/UNBOUNDED end ◇ EXCLUDE?) → ⊕ sql+params → ∑ '(...)' → ⟅string, array⟆

// region CLASS_Window [DOMAIN(9): Window; CONCEPT(9): WindowFunction; TECH(8): SQLAnalytics]
/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @phpstan-template TExpression of TValue|array<TExpression>
 *
 * @phpstan-template TCondition of TValue|array<TCondition>
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, bool|ExprInterface>
 *
 * @template TOrderBy of array<string, int|string>|array<int, string|ExprInterface|OrderColumn> column => order, expression or OrderColumn
 * @purpose Fluent builder for SQL window definitions (OVER clause).
 */
final class Window extends AbstractExpr
{
    private(set) ?string $existingWindowName = null;
    /**
     * @property ExprInterface[] $partitionBy
     */
    private(set) array $partitionBy = [];
    /**
     * @property OrderColumn[] $orderBy
     */
    private(set) array $orderBy = [];
    private(set) ?FrameTypeEnum $frameType = null;
    private(set) FrameBoundEnum $frameStart = FrameBoundEnum::Preceding;
    private(set) FrameBoundEnum $frameEnd = FrameBoundEnum::CurrentRow;
    private(set) ExprInterface|int|null $frameStartOffset = null;
    private(set) ExprInterface|int|null $frameEndOffset = null;
    private(set) ?FrameExclusionEnum $frameExclusion = null;

    // region METHOD_extend [DOMAIN(9): Window; CONCEPT(7): NamedWindow; TECH(5): FluentAPI]
    /**
     * @purpose Sets a named window to extend (WINDOW clause reference).
     * @io string -> Window
     * @complexity 1
     */
    public function extend(string $extendingWindowName): Window
    {
        $this->existingWindowName = $extendingWindowName;

        return $this;
    }
    // endregion METHOD_extend

    // region METHOD_partitionBy [DOMAIN(9): Window; CONCEPT(8): PartitionBy; TECH(5): FluentAPI]
    /**
     * @purpose Sets the PARTITION BY columns, replacing any existing partitions.
     * @param array<int|string, TExpression> $partitions
     *
     * @return Window
     * @io array -> Window
     * @complexity 1
     */
    public function partitionBy(array $partitions): Window
    {
        $this->partitionBy = $partitions;

        return $this;
    }
    // endregion METHOD_partitionBy

    // region METHOD_addPartitionBy [DOMAIN(9): Window; CONCEPT(8): PartitionBy; TECH(5): FluentAPI]
    /**
     * @purpose Appends PARTITION BY columns to existing partitions.
     * @param array<int|string, TExpression> $partitions
     *
     * @return Window
     * @io array -> Window
     * @complexity 2
     */
    public function addPartitionBy(array $partitions): Window
    {
        $this->partitionBy = array_merge($this->partitionBy, $partitions);

        return $this;
    }
    // endregion METHOD_addPartitionBy

    // region METHOD_orderBy [DOMAIN(9): Window; CONCEPT(8): OrderBy; TECH(5): FluentAPI]
    /**
     * @purpose Sets the ORDER BY columns for the window, replacing any existing order.
     * @param TOrderBy $columns
     *
     * @return Window
     * @io array -> Window
     * @complexity 2
     */
    public function orderBy(array $columns): Window
    {
        $this->orderBy = HExpr::normalizeOrderBy($columns);

        return $this;
    }
    // endregion METHOD_orderBy

    // region METHOD_addOrderBy [DOMAIN(9): Window; CONCEPT(8): OrderBy; TECH(5): FluentAPI]
    /**
     * @purpose Appends ORDER BY columns to existing window ordering.
     * @param TOrderBy $columns
     *
     * @return Window
     * @io array -> Window
     * @complexity 2
     */
    public function addOrderBy(array $columns): Window
    {
        $this->orderBy = array_merge(
            $this->orderBy,
            HExpr::normalizeOrderBy($columns),
        );

        return $this;
    }
    // endregion METHOD_addOrderBy

    // region METHOD_frame [DOMAIN(9): Window; CONCEPT(9): FrameSpec; TECH(7): FluentAPI]
    /**
     * @purpose Sets the frame specification (type, bounds, offset, exclusion) for the window.
     *
     * Be careful with frame boundaries, ensure they are valid for the chosen frame type.
     * You MUST check that frame boundaries don't contain SQL injections.
     *
     * Default frame options are RANGE UNBOUNDED PRECEDING,
     * which is the same as RANGE BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
     *
     * @param FrameTypeEnum $type
     * @param FrameBoundEnum $start
     * @param FrameBoundEnum $end
     * @param int|ExprInterface|null $startOffset
     * @param int|ExprInterface|null $endOffset
     * @param FrameExclusionEnum|null $exclusion
     *
     * @return Window
     * @io FrameTypeEnum, FrameBoundEnum, FrameBoundEnum, int|ExprInterface|null, int|ExprInterface|null, FrameExclusionEnum|null -> Window
     * @complexity 3
     */
    public function frame(
        FrameTypeEnum $type = FrameTypeEnum::Range,
        FrameBoundEnum $start = FrameBoundEnum::Preceding,
        FrameBoundEnum $end = FrameBoundEnum::CurrentRow,
        int|ExprInterface|null $startOffset = null,
        int|ExprInterface|null $endOffset = null,
        ?FrameExclusionEnum $exclusion = null,
    ): Window {
        $this->frameType = $type;
        $this->frameStart = $start;
        $this->frameEnd = $end;
        $this->frameStartOffset = $start !== FrameBoundEnum::CurrentRow ? $startOffset : null;
        $this->frameEndOffset = $end !== FrameBoundEnum::CurrentRow ? $endOffset : null;
        $this->frameExclusion = $exclusion;

        return $this;
    }
    // endregion METHOD_frame

    // region METHOD_range [DOMAIN(9): Window; CONCEPT(8): FrameSpec; TECH(5): FluentAPI]
    /**
     * @purpose Convenience method to set a RANGE-based frame (delegates to frame()).
     * @param FrameBoundEnum $start
     * @param FrameBoundEnum $end
     * @param int|ExprInterface|null $startOffset
     * @param int|ExprInterface|null $endOffset
     * @param FrameExclusionEnum|null $exclusion
     *
     * @return Window
     * @see Window::frame()
     * @io -> Window
     * @complexity 1
     */
    public function range(
        FrameBoundEnum $start = FrameBoundEnum::Preceding,
        FrameBoundEnum $end = FrameBoundEnum::CurrentRow,
        int|ExprInterface|null $startOffset = null,
        int|ExprInterface|null $endOffset = null,
        ?FrameExclusionEnum $exclusion = null,
    ): Window {
        return $this->frame(
            FrameTypeEnum::Range,
            $start,
            $end,
            $startOffset,
            $endOffset,
            $exclusion,
        );
    }
    // endregion METHOD_range

    // region METHOD_rows [DOMAIN(9): Window; CONCEPT(8): FrameSpec; TECH(5): FluentAPI]
    /**
     * @purpose Convenience method to set a ROWS-based frame (delegates to frame()).
     * @param FrameBoundEnum $start
     * @param FrameBoundEnum $end
     * @param int|ExprInterface|null $startOffset
     * @param int|ExprInterface|null $endOffset
     * @param FrameExclusionEnum|null $exclusion
     *
     * @return Window
     * @see Window::frame()
     * @io -> Window
     * @complexity 1
     */
    public function rows(
        FrameBoundEnum $start = FrameBoundEnum::Preceding,
        FrameBoundEnum $end = FrameBoundEnum::CurrentRow,
        int|ExprInterface|null $startOffset = null,
        int|ExprInterface|null $endOffset = null,
        ?FrameExclusionEnum $exclusion = null,
    ): Window {
        return $this->frame(
            FrameTypeEnum::Rows,
            $start,
            $end,
            $startOffset,
            $endOffset,
            $exclusion,
        );
    }
    // endregion METHOD_rows

    // region METHOD_groups [DOMAIN(9): Window; CONCEPT(8): FrameSpec; TECH(5): FluentAPI]
    /**
     * @purpose Convenience method to set a GROUPS-based frame (delegates to frame()).
     * @param FrameBoundEnum $start
     * @param FrameBoundEnum $end
     * @param int|ExprInterface|null $startOffset
     * @param int|ExprInterface|null $endOffset
     * @param FrameExclusionEnum|null $exclusion
     *
     * @return Window
     * @see Window::frame()
     * @io -> Window
     * @complexity 1
     */
    public function groups(
        FrameBoundEnum $start = FrameBoundEnum::Preceding,
        FrameBoundEnum $end = FrameBoundEnum::CurrentRow,
        int|ExprInterface|null $startOffset = null,
        int|ExprInterface|null $endOffset = null,
        ?FrameExclusionEnum $exclusion = null,
    ): Window {
        return $this->frame(
            FrameTypeEnum::Groups,
            $start,
            $end,
            $startOffset,
            $endOffset,
            $exclusion,
        );
    }
    // endregion METHOD_groups

    // region METHOD_doBuild [DOMAIN(9): Window; CONCEPT(9): SQLRender; TECH(7): GrammarInterface]
    /**
     * @purpose Assembles the full window definition SQL: [existing_name] [PARTITION BY ...] [ORDER BY ...] [frame].
     * @io GrammarInterface -> array{string, array}
     * @complexity 5
     */
    protected function doBuild(GrammarInterface $grammar): array
    {
        $parts = [];
        $params = [];

        if ($this->existingWindowName) {
            $parts[] = $grammar->escapeIdentifier($this->existingWindowName);
        }

        if ($this->partitionBy) {
            $expr = $this->buildPartitionBy($grammar);
            $parts[] = $expr->getExpression($grammar);
            $params = HExpr::mergeParams($params, $expr->getParams());
        }

        if ($this->orderBy) {
            $expr = $this->buildOrderBy($grammar);
            $parts[] = $expr->getExpression($grammar);
            $params = HExpr::mergeParams($params, $expr->getParams());
        }

        if ($this->frameType !== null) {
            $expr = $this->buildFrame($grammar);
            $parts[] = $expr->getExpression($grammar);
            $params = HExpr::mergeParams($params, $expr->getParams());
        }

        return ['(' . implode(' ', $parts) . ')', $params];
    }
    // endregion METHOD_doBuild

    // region METHOD_buildPartitionBy [DOMAIN(9): Window; CONCEPT(8): PartitionBy; TECH(6): SQLBuild]
    /**
     * @purpose Builds the PARTITION BY clause SQL from partition column definitions.
     * @io GrammarInterface -> ExprInterface
     * @complexity 4
     */
    private function buildPartitionBy(GrammarInterface $grammar): ExprInterface
    {
        $partitionBy = [];
        $params = [];
        $vb = new ValueBuilder();
        foreach ($this->partitionBy as $column) {
            if (is_string($column)) {
                // Group by column name
                $column = new Expr($grammar->escapeIdentifierDotted($column));
            } else {
                $column = $vb->build($column, $grammar);
            }

            $partitionBy[] = $column->getExpression($grammar);
            $params = HExpr::mergeParams($params, $column->getParams());
        }

        return new Expr(
            sprintf(
                'PARTITION BY %s',
                implode(', ', $partitionBy),
            ),
            $params,
        );
    }
    // endregion METHOD_buildPartitionBy

    // region METHOD_buildOrderBy [DOMAIN(9): Window; CONCEPT(8): OrderBy; TECH(6): SQLBuild]
    /**
     * @purpose Builds the ORDER BY clause SQL from OrderColumn definitions.
     * @param OrderColumn[] $columns
     * @param GrammarInterface $grammar
     *
     * @return ExprInterface
     * @io GrammarInterface -> ExprInterface
     * @complexity 4
     */
    private function buildOrderBy(GrammarInterface $grammar): ExprInterface
    {
        $expressions = [];
        $params = [];
        $vb = new ValueBuilder();
        foreach ($this->orderBy as $column) {
            $sortType = $column->order;
            $sortBy = $column->expr;
            if (is_string($sortBy)) {
                // Order by column name
                $column = new Expr($grammar->escapeIdentifierDotted($sortBy));
            } else {
                $column = $vb->build($sortBy, $grammar);
            }

            $expr = $column->getExpression($grammar);
            $expressions[] = "$expr $sortType";
            $params = HExpr::mergeParams($params, $column->getParams());
        }

        return new Expr(
            sprintf(
                'ORDER BY %s',
                implode(', ', $expressions),
            ),
            $params,
        );
    }
    // endregion METHOD_buildOrderBy

    // region METHOD_buildFrame [DOMAIN(9): Window; CONCEPT(9): FrameSpec; TECH(6): SQLBuild]
    /**
     * @purpose Builds the frame specification SQL (RANGE/ROWS/GROUPS BETWEEN ... AND ... [EXCLUDE ...]).
     * @io GrammarInterface -> ExprInterface
     * @complexity 6
     */
    private function buildFrame(GrammarInterface $grammar): ExprInterface
    {
        $parts = [];
        $params = [];
        $vb = new ValueBuilder();

        $parts[] = $this->frameType->getSql();

        $parts[] = 'BETWEEN';
        if ($this->frameStartOffset !== null) {
            $frameStartOffset = $vb->build($this->frameStartOffset, $grammar);
            $parts[] = $frameStartOffset->getExpression($grammar);
            $params = HExpr::mergeParams($params, $frameStartOffset->getParams());
        } elseif ($this->frameStart !== FrameBoundEnum::CurrentRow) {
            $parts[] = 'UNBOUNDED';
        }

        $parts[] = $this->frameStart->getSql();

        $parts[] = 'AND';
        if ($this->frameEndOffset !== null) {
            $frameEndOffset = $vb->build($this->frameEndOffset, $grammar);
            $parts[] = $frameEndOffset->getExpression($grammar);
            $params = HExpr::mergeParams($params, $frameEndOffset->getParams());
        } elseif ($this->frameEnd !== FrameBoundEnum::CurrentRow) {
            $parts[] = 'UNBOUNDED';
        }

        $parts[] = $this->frameEnd->getSql();

        if ($this->frameExclusion !== null) {
            $parts[] = 'EXCLUDE';
            $parts[] = $this->frameExclusion->getSql();
        }

        return new Expr(
            implode(' ', $parts),
            $params,
        );
    }
    // endregion METHOD_buildFrame
}
// endregion CLASS_Window
