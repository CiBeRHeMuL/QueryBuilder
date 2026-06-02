<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Query\Insert\MySql;

use AndrewGos\QueryBuilder\Query\Insert\InsertQuery;
use AndrewGos\QueryBuilder\Query\Interface\MySql\PartitionInterface;
use AndrewGos\QueryBuilder\Query\Trait\MySql\PartitionTrait;

// region MODULE_CONTRACT [DOMAIN(8): Insert; CONCEPT(8): MySqlInsertQuery; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose MySQL-specific INSERT query with LOW_PRIORITY, DELAYED, HIGH_PRIORITY, IGNORE modifiers and PARTITION support.
 * @scope MySQL INSERT query building.
 * @input Table, columns, values, modifiers, partitions
 * @output MySqlInsertQuery instance with MySQL-specific INSERT capabilities
 * @invariants
 * - Implements PartitionInterface for MySQL partition clause
 * @modulemap
 * MySqlInsertQuery => MySQL INSERT query with modifiers and PARTITION
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MySQL, INSERT, LOW_PRIORITY, DELAYED, HIGH_PRIORITY, IGNORE, PARTITION, dialect

// region CLASS_MySqlInsertQuery [DOMAIN(8): Insert; CONCEPT(8): MySqlInsertQuery; TECH(8): Dialect]
/**
 * @purpose MySQL INSERT query extending InsertQuery with LOW_PRIORITY, DELAYED, HIGH_PRIORITY, IGNORE modifiers and PARTITION clause support.
 */
class MySqlInsertQuery extends InsertQuery implements PartitionInterface
{
    use PartitionTrait;

    protected(set) bool $lowPriority = false;
    protected(set) bool $delayed = false;
    protected(set) bool $highPriority = false;
    protected(set) bool $ignore = false;

    // region METHOD_lowPriority [DOMAIN(8): Insert; TECH(8): MySQLModifiers]
    /**
     * @purpose Set MySQL LOW_PRIORITY modifier for INSERT.
     */
    public function lowPriority(bool $lowPriority = true): static
    {
        $this->lowPriority = $lowPriority;

        return $this;
    }
    // endregion METHOD_lowPriority

    // region METHOD_delayed [DOMAIN(8): Insert; TECH(8): MySQLModifiers]
    /**
     * @purpose Set MySQL DELAYED modifier for INSERT.
     */
    public function delayed(bool $delayed = true): static
    {
        $this->delayed = $delayed;

        return $this;
    }
    // endregion METHOD_delayed

    // region METHOD_highPriority [DOMAIN(8): Insert; TECH(8): MySQLModifiers]
    /**
     * @purpose Set MySQL HIGH_PRIORITY modifier for INSERT.
     */
    public function highPriority(bool $highPriority = true): static
    {
        $this->highPriority = $highPriority;

        return $this;
    }
    // endregion METHOD_highPriority

    // region METHOD_ignore [DOMAIN(8): Insert; TECH(8): MySQLModifiers]
    /**
     * @purpose Set MySQL IGNORE modifier for INSERT.
     */
    public function ignore(bool $ignore = true): static
    {
        $this->ignore = $ignore;

        return $this;
    }
    // endregion METHOD_ignore
}
// endregion CLASS_MySqlInsertQuery
