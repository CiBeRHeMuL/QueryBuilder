<?php

namespace AndrewGos\QueryBuilder\Query\Delete\MySql;

use AndrewGos\QueryBuilder\Query\Delete\DeleteQuery;
use AndrewGos\QueryBuilder\Query\Interface\MySql\PartitionInterface;
use AndrewGos\QueryBuilder\Query\Trait\FromTrait;
use AndrewGos\QueryBuilder\Query\Trait\LimitTrait;
use AndrewGos\QueryBuilder\Query\Trait\MySql\PartitionTrait;
use AndrewGos\QueryBuilder\Query\Trait\OrderByTrait;

class MySqlDeleteQuery extends DeleteQuery implements PartitionInterface
{
    use FromTrait;
    use OrderByTrait;
    use LimitTrait;
    use PartitionTrait;

    protected(set) bool $lowPriority = false;
    protected(set) bool $quick = false;
    protected(set) bool $ignore = false;

    public function lowPriority(bool $lowPriority = true): static
    {
        $this->lowPriority = $lowPriority;

        return $this;
    }

    public function quick(bool $quick = true): static
    {
        $this->quick = $quick;

        return $this;
    }

    public function ignore(bool $ignore = true): static
    {
        $this->ignore = $ignore;

        return $this;
    }
}
