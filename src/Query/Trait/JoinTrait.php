<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Trait;

use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Join\JoinTable;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * @moduleContract
 * @purpose Implement JoinInterface for full JOIN clause support via reusable trait.
 * @scope Handles all join types (INNER, LEFT, RIGHT, CROSS, FULL, NATURAL variants).
 * @input Join type, table reference, conditions, optional alias.
 * @output Normalized JOIN clause state via JoinInterface contract.
 * @modulemap
 * TRAIT JoinTrait => JoinInterface implementation
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: JOIN, trait, SQL, INNER, LEFT, RIGHT, CROSS, FULL, NATURAL
// STRUCTURE: ▶ join() + innerJoin() + leftJoin() + rightJoin() + crossJoin() + fullJoin() + natural*Join() → ∑ [JoinTrait methods]

// region TRAIT_JoinTrait [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
/**
 * This trait provides functionality of JoinInterface.
 *
 * @see \AndrewGos\QueryBuilder\Query\Interface\JoinInterface
 *
 * @purpose Implement JoinInterface for queries requiring JOIN support.
 */
trait JoinTrait
{
    /**
     * {@inheritDoc}
     */
    protected(set) array $joinTables = [];

    // region METHOD_join [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create a JOIN of the specified type with HExpr::normalizeTable, optionally keyed by alias.
     * @io JoinTypeEnum $type, TTable $table, array $conditions, ?string $alias -> static
     * @complexity 3
     *
     * {@inheritDoc}
     */
    public function join(
        JoinTypeEnum $type,
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        $joinTable = new JoinTable(
            $type,
            HExpr::normalizeTable($table),
            $conditions,
            false,
        );

        if ($alias !== null) {
            $this->joinTables[$alias] = $joinTable;
        } else {
            $this->joinTables[] = $joinTable;
        }

        return $this;
    }

    // endregion METHOD_join

    // region METHOD_innerJoin [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create an INNER JOIN, delegating to join() with InnerJoin type.
     * @io TTable $table, array $conditions, ?string $alias -> static
     * @complexity 2
     *
     * {@inheritDoc}
     */
    public function innerJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        return $this->join(
            JoinTypeEnum::InnerJoin,
            $table,
            $conditions,
            $alias,
        );
    }
    // endregion METHOD_innerJoin

    // region METHOD_leftJoin [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create a LEFT OUTER JOIN, delegating to join() with LeftOuterJoin type.
     * @io TTable $table, array $conditions, ?string $alias -> static
     * @complexity 2
     *
     * {@inheritDoc}
     */
    public function leftJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        return $this->join(
            JoinTypeEnum::LeftOuterJoin,
            $table,
            $conditions,
            $alias,
        );
    }
    // endregion METHOD_leftJoin

    // region METHOD_rightJoin [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create a RIGHT OUTER JOIN, delegating to join() with RightOuterJoin type.
     * @io TTable $table, array $conditions, ?string $alias -> static
     * @complexity 2
     *
     * {@inheritDoc}
     */
    public function rightJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        return $this->join(
            JoinTypeEnum::RightOuterJoin,
            $table,
            $conditions,
            $alias,
        );
    }
    // endregion METHOD_rightJoin

    // region METHOD_crossJoin [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create a CROSS JOIN, delegating to join() with CrossJoin type and empty conditions.
     * @io TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * {@inheritDoc}
     */
    public function crossJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        return $this->join(
            JoinTypeEnum::CrossJoin,
            $table,
            [],
            $alias,
        );
    }
    // endregion METHOD_crossJoin

    // region METHOD_fullJoin [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create a FULL OUTER JOIN, delegating to join() with FullOuterJoin type.
     * @io TTable $table, array $conditions, ?string $alias -> static
     * @complexity 2
     *
     * {@inheritDoc}
     */
    public function fullJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        array $conditions,
        ?string $alias = null,
    ): static {
        return $this->join(
            JoinTypeEnum::FullOuterJoin,
            $table,
            $conditions,
            $alias,
        );
    }
    // endregion METHOD_fullJoin

    // region METHOD_naturalJoin [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create a NATURAL JOIN, validating that cross join is not used with natural.
     * @io JoinTypeEnum $type, TTable $table, ?string $alias -> static
     * @complexity 3
     *
     * @throws QueryBuilderException
     *
     * {@inheritDoc}
     */
    public function naturalJoin(
        JoinTypeEnum $type,
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        if ($type === JoinTypeEnum::CrossJoin) {
            throw QueryBuilderException::invalidNaturalJoinType($type);
        }

        $joinTable = new JoinTable(
            $type,
            HExpr::normalizeTable($table),
            naturalJoin: true,
        );

        if ($alias !== null) {
            $this->joinTables[$alias] = $joinTable;
        } else {
            $this->joinTables[] = $joinTable;
        }

        return $this;
    }
    // endregion METHOD_naturalJoin

    // region METHOD_naturalInnerJoin [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create a NATURAL INNER JOIN via naturalJoin().
     * @io TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * {@inheritDoc}
     */
    public function naturalInnerJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        return $this->naturalJoin(
            JoinTypeEnum::InnerJoin,
            $table,
            $alias,
        );
    }
    // endregion METHOD_naturalInnerJoin

    // region METHOD_naturalLeftJoin [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create a NATURAL LEFT OUTER JOIN via naturalJoin().
     * @io TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * {@inheritDoc}
     */
    public function naturalLeftJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        return $this->naturalJoin(
            JoinTypeEnum::LeftOuterJoin,
            $table,
            $alias,
        );
    }
    // endregion METHOD_naturalLeftJoin

    // region METHOD_naturalRightJoin [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create a NATURAL RIGHT OUTER JOIN via naturalJoin().
     * @io TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * {@inheritDoc}
     */
    public function naturalRightJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        return $this->naturalJoin(
            JoinTypeEnum::RightOuterJoin,
            $table,
            $alias,
        );
    }
    // endregion METHOD_naturalRightJoin

    // region METHOD_naturalFullJoin [DOMAIN(8): Query; CONCEPT(9): Trait; TECH(8): SQL]
    /**
     * @purpose Create a NATURAL FULL OUTER JOIN via naturalJoin().
     * @io TTable $table, ?string $alias -> static
     * @complexity 2
     *
     * {@inheritDoc}
     */
    public function naturalFullJoin(
        string|ExprInterface|SelectQueryInterface|ValuesQueryInterface|SelectTable $table,
        ?string $alias = null,
    ): static {
        return $this->naturalJoin(
            JoinTypeEnum::FullOuterJoin,
            $table,
            $alias,
        );
    }
    // endregion METHOD_naturalFullJoin
}
// endregion TRAIT_JoinTrait
