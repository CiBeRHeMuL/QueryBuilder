<?php

namespace AndrewGos\QueryBuilder\Query\Trait\PgSql;

use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\OrExpr;

/**
 * This trait provides functionality of Pg\ReturningInterface
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\PgSql\ReturningInterface
 */
trait ReturningTrait
{
    protected(set) ?string $returningOldAlias = null;
    protected(set) ?string $returningNewAlias = null;
    /**
     * @inheritDoc
     */
    protected(set) ?array $returningColumns = null;

    /**
     * @inheritDoc
     */
    public function returning(array $columns, ?string $oldAlias = null, ?string $newAlias = null): static
    {
        $this->returningColumns = $columns;
        $this->returningOldAlias = $oldAlias;
        $this->returningNewAlias = $newAlias;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addReturning(array $columns, ?string $oldAlias = null, ?string $newAlias = null): static
    {
        $this->returningColumns = array_merge($this->returningColumns ?? [], $columns);
        $this->returningOldAlias = $oldAlias;
        $this->returningNewAlias = $newAlias;

        return $this;
    }
}
