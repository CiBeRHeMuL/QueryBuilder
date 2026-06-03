<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr\Update\PgSql;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): UPDATE; CONCEPT(8): PgSqlSetClause; TECH(6): Dialect]
/**
 * @moduleContract
 * @purpose PostgreSQL-specific SET clause extension with ROW() syntax support for multi-column assignments.
 * @scope Extends SetClause with isRow flag for ROW(expr1, expr2, ...) syntax.
 * @input Target column(s), value, and isRow flag.
 * @output PostgreSQL-specific SET clause value object.
 * @modulemap
 * PgSqlSetClause => PgSQL SET clause with ROW support
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, SET, ROW, multi-column, dialect
// STRUCTURE: ◇ isRow && array target? → ⚡ ROW(value) syntax → ⊕ Expr | ✗ → parent::getSql()

// region CLASS_PgSqlSetClause [DOMAIN(8): UPDATE; CONCEPT(8): PgSqlSetClause; TECH(6): Dialect]
/**
 * @purpose PostgreSQL SET clause that supports ROW() syntax for multi-column assignments. Use isRow=true to emit ROW(val1, val2) instead of (val1, val2).
 */
readonly class PgSqlSetClause extends SetClause
{
    protected(set) bool $isRow;

    /**
     * @param string|string[] $target
     * @param bool|int|float|string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array $value
     * @param bool $isRow Enable PostgreSQL ROW() syntax for multi-column assignments.
     */
    public function __construct(
        string|array $target,
        bool|int|float|string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array $value,
        bool $isRow = false,
    ) {
        parent::__construct($target, $value);
        $this->isRow = $isRow;
    }

    // region METHOD_getSql [DOMAIN(8): UPDATE; CONCEPT(8): PgSqlSetClause; TECH(7): Rendering]
    /**
     * @purpose Render this PgSQL SET clause element. For isRow=true multi-column, produces ROW(val1, val2) syntax.
     * @param GrammarInterface $grammar Grammar for identifier escaping.
     * @return ExprInterface The rendered expression with ROW syntax when applicable.
     */
    public function getSql(GrammarInterface $grammar): ExprInterface
    {
        if ($this->isRow && is_array($this->target)) {
            $vb = new ValueBuilder();
            $escapedTargets = array_map(
                fn(string $t): string => $grammar->escapeIdentifier($t),
                $this->target,
            );
            $targetPart = '(' . implode(', ', $escapedTargets) . ')';
            $expr = $vb->build($this->value, $grammar);
            $valueStr = is_array($this->value)
                ? 'ROW' . $expr->getExpression($grammar)
                : 'ROW(' . $expr->getExpression($grammar) . ')';

            return new Expr($targetPart . ' = ' . $valueStr, $expr->getParams());
        }

        return parent::getSql($grammar);
    }
    // endregion METHOD_getSql
}
// endregion CLASS_PgSqlSetClause
