<?php

namespace AndrewGos\QueryBuilder\Expr\Window;

use AndrewGos\QueryBuilder\Enum\Window\FrameBoundEnum;
use AndrewGos\QueryBuilder\Enum\Window\FrameExclusionEnum;
use AndrewGos\QueryBuilder\Enum\Window\FrameTypeEnum;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\AbstractExpr;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Order\OrderColumn;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use UnitEnum;

/**
 * @template TValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|null
 * @phpstan-template TExpression of TValue|array<TExpression>
 *
 * @phpstan-template TCondition of TValue|array<TCondition>
 * @template TStandaloneCondition of bool|ExprInterface
 * @template TConditions of array<string, TCondition>|array<int, bool|ExprInterface>
 *
 * @template TOrderBy of array<string, int|string>|array<int, string|ExprInterface|OrderColumn> column => order, expression or OrderColumn
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

    public function extend(string $extendingWindowName): Window
    {
        $this->existingWindowName = $extendingWindowName;

        return $this;
    }

    /**
     * @param array<int|string, TExpression> $partitions
     *
     * @return Window
     */
    public function partitionBy(array $partitions): Window
    {
        $this->partitionBy = $partitions;

        return $this;
    }

    /**
     * @param array<int|string, TExpression> $partitions
     *
     * @return Window
     */
    public function addPartitionBy(array $partitions): Window
    {
        $this->partitionBy = array_merge($this->partitionBy, $partitions);

        return $this;
    }

    /**
     * @param TOrderBy $columns
     *
     * @return Window
     */
    public function orderBy(array $columns): Window
    {
        $this->orderBy = HExpr::normalizeOrderBy($columns);

        return $this;
    }

    /**
     * @param TOrderBy $columns
     *
     * @return Window
     */
    public function addOrderBy(array $columns): Window
    {
        $this->orderBy = array_merge(
            $this->orderBy,
            HExpr::normalizeOrderBy($columns),
        );

        return $this;
    }

    /**
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

    /**
     * @param FrameBoundEnum $start
     * @param FrameBoundEnum $end
     * @param int|ExprInterface|null $startOffset
     * @param int|ExprInterface|null $endOffset
     * @param FrameExclusionEnum|null $exclusion
     *
     * @return Window
     * @see Window::frame()
     *
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

    /**
     * @param FrameBoundEnum $start
     * @param FrameBoundEnum $end
     * @param int|ExprInterface|null $startOffset
     * @param int|ExprInterface|null $endOffset
     * @param FrameExclusionEnum|null $exclusion
     *
     * @return Window
     * @see Window::frame()
     *
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

    /**
     * @param FrameBoundEnum $start
     * @param FrameBoundEnum $end
     * @param int|ExprInterface|null $startOffset
     * @param int|ExprInterface|null $endOffset
     * @param FrameExclusionEnum|null $exclusion
     *
     * @return Window
     * @see Window::frame()
     *
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

    /**
     * @param OrderColumn[] $columns
     * @param GrammarInterface $grammar
     *
     * @return ExprInterface
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
}
