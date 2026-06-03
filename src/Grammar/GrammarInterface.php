<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Grammar;

use AndrewGos\QueryBuilder\Query\Delete\DeleteQueryInterface;
use AndrewGos\QueryBuilder\Query\Insert\InsertQueryInterface;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQueryInterface;
use AndrewGos\QueryBuilder\Query\Update\UpdateQueryInterface;
use AndrewGos\QueryBuilder\Query\Values\ValuesQueryInterface;

// region MODULE_CONTRACT [DOMAIN(9): Grammar; CONCEPT(9): Contract; TECH(9): Interface]
/**
 * @moduleContract
 * @purpose Defines the contract for all SQL grammar implementations (Default/ANSI, MySQL, PostgreSQL).
 * @scope Query building API, identifier escaping.
 * @input Query interfaces (Select, Insert, Update, Delete, Values)
 * @output BuiltQuery instances
 * @invariants
 * - All implementations must support the same method signatures.
 * @modulemap
 * GrammarInterface => Contract for SQL dialect grammars
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: GrammarInterface, SQL dialect, grammar contract, query building
// STRUCTURE: ▶ build*Query + escapeIdentifier* → ∑ [GrammarInterface contract]

// region INTERFACE_GrammarInterface [DOMAIN(9): Grammar; CONCEPT(9): Contract; TECH(9): Interface]
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
// endregion INTERFACE_GrammarInterface
