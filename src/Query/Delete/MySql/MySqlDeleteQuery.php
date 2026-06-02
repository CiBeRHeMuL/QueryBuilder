<?php

namespace AndrewGos\QueryBuilder\Query\Delete\MySql;

use AndrewGos\QueryBuilder\Query\Delete\DeleteQuery;
use AndrewGos\QueryBuilder\Query\Interface\MySql\PartitionInterface;
use AndrewGos\QueryBuilder\Query\Trait\FromTrait;
use AndrewGos\QueryBuilder\Query\Trait\LimitTrait;
use AndrewGos\QueryBuilder\Query\Trait\MySql\PartitionTrait;
use AndrewGos\QueryBuilder\Query\Trait\OrderByTrait;

// region MODULE_CONTRACT [DOMAIN(8): Delete; CONCEPT(8): MySqlDeleteQuery; TECH(8): Dialect]
/**
 * @moduleContract
 * @purpose MySQL-specific DELETE query with LOW_PRIORITY, QUICK, IGNORE modifiers and PARTITION, ORDER BY, LIMIT support.
 * @scope MySQL DELETE query building.
 * @input Tables, conditions, partitions, order, limit
 * @output MySqlDeleteQuery instance with MySQL-specific DELETE capabilities
 * @invariants
 * - Implements PartitionInterface for MySQL partition pruning
 * @modulemap
 * MySqlDeleteQuery => MySQL DELETE query with MySQL-specific modifiers
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: MySQL, DELETE, LOW_PRIORITY, QUICK, IGNORE, PARTITION, dialect

// region CLASS_MySqlDeleteQuery [DOMAIN(8): Delete; CONCEPT(8): MySqlDeleteQuery; TECH(8): Dialect]
/**
 * @purpose MySQL DELETE query extending DeleteQuery with LOW_PRIORITY, QUICK, IGNORE flags, PARTITION, ORDER BY, and LIMIT support.
 */
class MySqlDeleteQuery extends DeleteQuery implements PartitionInterface
{
    use FromTrait;
    use OrderByTrait;
    use LimitTrait;
    use PartitionTrait;

    protected(set) bool $lowPriority = false;
    protected(set) bool $quick = false;
    protected(set) bool $ignore = false;

    // region METHOD_lowPriority [DOMAIN(8): Delete; TECH(8): MySQLModifiers]
    /**
     * @purpose Set MySQL LOW_PRIORITY modifier for DELETE.
     */
    public function lowPriority(bool $lowPriority = true): static
    {
        $this->lowPriority = $lowPriority;

        return $this;
    }
    // endregion METHOD_lowPriority

    // region METHOD_quick [DOMAIN(8): Delete; TECH(8): MySQLModifiers]
    /**
     * @purpose Set MySQL QUICK modifier for DELETE.
     */
    public function quick(bool $quick = true): static
    {
        $this->quick = $quick;

        return $this;
    }
    // endregion METHOD_quick

    // region METHOD_ignore [DOMAIN(8): Delete; TECH(8): MySQLModifiers]
    /**
     * @purpose Set MySQL IGNORE modifier for DELETE.
     */
    public function ignore(bool $ignore = true): static
    {
        $this->ignore = $ignore;

        return $this;
    }
    // endregion METHOD_ignore
}
// endregion CLASS_MySqlDeleteQuery
