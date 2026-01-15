<?php

namespace AndrewGos\QueryBuilder\Query\Delete;

use AndrewGos\QueryBuilder\Query\Trait\SingleFromTrait;
use AndrewGos\QueryBuilder\Query\Trait\WhereTrait;
use AndrewGos\QueryBuilder\Query\Trait\WithTrait;

class DeleteQuery implements DeleteQueryInterface
{
    use WithTrait;
    use SingleFromTrait;
    use WhereTrait;
}
