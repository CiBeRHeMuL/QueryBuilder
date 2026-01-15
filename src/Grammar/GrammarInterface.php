<?php

namespace AndrewGos\QueryBuilder\Grammar;

use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Delete\DeleteQueryInterface;
use AndrewGos\QueryBuilder\Query\Update\UpdateQueryInterface;
use AndrewGos\QueryBuilder\Query\Insert\InsertQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

interface GrammarInterface
{
    public function buildSelectQuery(SelectQueryInterface $query): BuiltQuery;

    public function buildValuesQuery(ValuesQueryInterface $query): BuiltQuery;

    public function buildMaybeReturnableQuery(MaybeReturnableQueryInterface $query): BuiltQuery;

    public function buildDeleteQuery(DeleteQueryInterface $query): BuiltQuery;

    public function buildInsertQuery(InsertQueryInterface $query): BuiltQuery;

    public function buildUpdateQuery(UpdateQueryInterface $query): BuiltQuery;

    public function escapeIdentifier(string $identifier): string;

    /**
     * Escapes identifier. Can work with identifiers with column aliases such as `table.col1`
     *
     * @param string $identifier
     *
     * @return string
     */
    public function escapeIdentifierDotted(string $identifier): string;

    /**
     * Escapes table alias. Can work with aliases with column aliases such as `table(col1, col2, ...)`
     *
     * @param string $alias
     *
     * @return string
     */
    public function escapeTableAlias(string $alias): string;
}
