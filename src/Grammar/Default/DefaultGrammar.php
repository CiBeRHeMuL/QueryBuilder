<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Grammar\Default;

use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;

// region MODULE_CONTRACT [DOMAIN(7): Grammar; CONCEPT(6): DefaultGrammar; TECH(7): ANSI_SQL]
/**
 * @moduleContract
 * @purpose Universal ANSI SQL grammar with double-quote identifier escaping. Suitable for any SQL dialect that follows the ANSI SQL standard for identifier quoting.
 * @scope SQL query building with standard identifier escaping.
 * @input Query objects (SelectQueryInterface, ValuesQueryInterface, DeleteQueryInterface, InsertQueryInterface, UpdateQueryInterface)
 * @output BuiltQuery with ANSI SQL-compatible SQL and parameters
 * @invariants
 * - Identifiers are escaped with double quotes per ANSI SQL standard
 * - Wildcard '*' is returned unquoted
 * - No dialect-specific query extensions or modifiers
 *
 * @rationale
 * Q: Why does DefaultGrammar exist when PgSqlGrammar uses the same double-quote escaping?
 * A: DefaultGrammar is a lightweight, dependency-free grammar with no PostgreSQL-specific features (RETURNING, DISTINCT ON, ONLY, USING, CTE materialization, lock modes). It works with base Query interfaces only and serves as the universal fallback grammar.
 *
 * @modulemap
 * DefaultGrammar => ANSI SQL default grammar extending AbstractGrammar
 *
 * @usecases
 * - [DefaultGrammar]: QueryBuilder → Build any query → ANSI SQL-compatible BuiltQuery
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: DefaultGrammar, ANSI SQL, grammar, identifier escaping, double quotes, default dialect
// STRUCTURE: ▶ ┌identifier┐ → ◇ identifier === '*' ? → ⚡ return '*' └─ else → ⚡ trim(identifier) → ⚡ '"' . strtr(identifier, '"' → '""') . '"' → ∑ return

// region CLASS_DefaultGrammar [DOMAIN(7): Grammar; CONCEPT(6): DefaultGrammar; TECH(7): ANSI_SQL]
/**
 * @purpose Minimal ANSI SQL grammar with double-quote identifier escaping. No dialect-specific extensions — works with all base Query interfaces.
 */
class DefaultGrammar extends AbstractGrammar
{
    // region METHOD_escapeIdentifier [DOMAIN(7): Grammar; TECH(7): IdentifierEscaping]
    /**
     * @purpose Escape identifier with ANSI SQL double quotes. Wildcard '*' is returned unquoted.
     * @io string $identifier -> string
     * @complexity 2
     */
    public function escapeIdentifier(string $identifier): string
    {
        if ($identifier === '*') {
            return $identifier;
        }

        $identifier = trim($identifier, " \n\r\t\v\0\"");

        return '"' . strtr($identifier, ['"' => '""']) . '"';
    }
    // endregion METHOD_escapeIdentifier
}
// endregion CLASS_DefaultGrammar
