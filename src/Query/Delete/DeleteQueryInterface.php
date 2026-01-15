<?php

namespace AndrewGos\QueryBuilder\Query\Delete;

use AndrewGos\QueryBuilder\Query\Interface\FromInterface;
use AndrewGos\QueryBuilder\Query\Interface\WhereInterface;
use AndrewGos\QueryBuilder\Query\Interface\WithInterface;

interface DeleteQueryInterface extends WithInterface, FromInterface, WhereInterface {}
