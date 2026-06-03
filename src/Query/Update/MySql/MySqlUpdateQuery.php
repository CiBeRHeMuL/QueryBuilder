<?php

namespace AndrewGos\QueryBuilder\Query\Update\MySql;

use AndrewGos\QueryBuilder\Expr\ExprInterface;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Query\Interface\MySql\PartitionInterface;
use AndrewGos\QueryBuilder\Query\Interface\OrderByInterface;
use AndrewGos\QueryBuilder\Query\Interface\LimitInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Trait\LimitTrait;
use AndrewGos\QueryBuilder\Query\Trait\MySql\PartitionTrait;
use AndrewGos\QueryBuilder\Query\Trait\OrderByTrait;
use AndrewGos\QueryBuilder\Query\Update\UpdateQuery;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;
use UnitEnum;

// region MODULE_CONTRACT [DOMAIN(8): Update; CONCEPT(8): MySqlUpdateQuery; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose MySQL-specific UPDATE query with multi-table support (addTable()), PARTITION, ORDER BY, LIMIT, LOW_PRIORITY, and IGNORE flags.
 * @scope MySQL UPDATE query building.
 * @input Tables, SET values, conditions, partitions, order, limit
 * @output MySqlUpdateQuery instance with MySQL-specific UPDATE capabilities
 * @invariants
 * - $table (interface property) is synchronized with $tables array
 * - $table getter returns first element of $tables or empty string
 * - $table setter resets $tables to a single-element array
 * - Implements PartitionInterface for MySQL partition pruning
 * @modulemap
 * MySqlUpdateQuery => MySQL UPDATE query with multi-table support
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MySQL, UPDATE, LOW_PRIORITY, IGNORE, PARTITION, multi-table, dialect

// region CLASS_MySqlUpdateQuery [DOMAIN(8): Update; CONCEPT(8): MySqlUpdateQuery; TECH(8): Dialect]
/**
 * @template TSetValue of bool|int|float|string|UnitEnum|ExprInterface|SelectQueryInterface|ValuesQueryInterface|array|null
 * @template TSet of array<int, SetClause>|array<string, TSetValue>
 * @purpose MySQL UPDATE query extending UpdateQuery with multi-table support, PARTITION, ORDER BY, LIMIT, LOW_PRIORITY, and IGNORE flags.
 */
class MySqlUpdateQuery extends UpdateQuery implements
    OrderByInterface,
    LimitInterface,
    PartitionInterface
{
    use OrderByTrait;
    use LimitTrait;
    use PartitionTrait;

    /** @var string[] All tables for multi-table MySQL UPDATE */
    protected(set) array $tables = [];

    /**
     * Interface property $table — synchronized with $tables array.
     * Setter resets $tables to [$value]; getter returns first element.
     */
    protected(set) string $table {
        set {
            $this->tables = [$value];
        }
        get => $this->tables[0] ?? '';
    }

    protected(set) bool $lowPriority = false;
    protected(set) bool $ignore = false;

    // region METHOD_addTable [DOMAIN(8): Update; TECH(8): MultiTable]
    /**
     * @purpose Add an additional table to the MySQL UPDATE (multi-table syntax without FROM).
     * @param string $table Additional table name appended to $tables.
     * @return static
     * @complexity 1
     */
    public function addTable(string $table): static
    {
        $this->tables[] = $table;

        return $this;
    }
    // endregion METHOD_addTable

    // region METHOD_lowPriority [DOMAIN(8): Update; TECH(8): MySQLModifiers]
    /**
     * @purpose Set MySQL LOW_PRIORITY modifier for UPDATE.
     * @param bool $lowPriority When true, emits LOW_PRIORITY after UPDATE.
     * @return static
     * @complexity 1
     */
    public function lowPriority(bool $lowPriority = true): static
    {
        $this->lowPriority = $lowPriority;

        return $this;
    }
    // endregion METHOD_lowPriority

    // region METHOD_ignore [DOMAIN(8): Update; TECH(8): MySQLModifiers]
    /**
     * @purpose Set MySQL IGNORE modifier for UPDATE.
     * @param bool $ignore When true, emits IGNORE after UPDATE.
     * @return static
     * @complexity 1
     */
    public function ignore(bool $ignore = true): static
    {
        $this->ignore = $ignore;

        return $this;
    }
    // endregion METHOD_ignore
}
// endregion CLASS_MySqlUpdateQuery
