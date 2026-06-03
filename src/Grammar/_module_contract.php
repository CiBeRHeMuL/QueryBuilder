<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Grammar namespace — provides SQL dialect grammar abstraction for query building.
 * @scope SELECT, VALUES, DELETE, INSERT, UPDATE building; CTE/WITH, JOIN, WHERE, GROUP BY, HAVING, WINDOW, SET operations, ORDER BY, LIMIT, LOCK; identifier escaping.
 * @input Query interfaces
 * @output BuiltQuery (SQL + params)
 * @modulemap
 * GrammarInterface [10][Contract for all SQL dialect grammars] => GrammarInterface.php
 * AbstractGrammar [10][Abstract base with standard pipeline] => AbstractGrammar.php
 * BuiltQuery [7][Immutable SQL + params result container] => BuiltQuery.php
 * DefaultGrammar [6][ANSI SQL default grammar with double-quote escaping] => Default/DefaultGrammar.php
 * @usecases
 * - GrammarInterface: Client → Build query → BuiltQuery
 * - AbstractGrammar: Concrete grammar → Extend pipeline → Dialect-specific SQL
 */
