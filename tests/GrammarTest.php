<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Query\Delete\PgSql\PgSqlDeleteQuery;
use AndrewGos\QueryBuilder\Query\Insert\PgSql\PgSqlInsertQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Values\ValuesQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_GrammarTest [DOMAIN(9): Testing; CONCEPT(9): Grammar; TECH(9): Escaping]
/**
 * @purpose Test grammar-level features: escapeIdentifier, escapeIdentifierDotted, escapeTableAlias for PgSql and MySql, and buildMaybeReturnableQuery.
 */
class GrammarTest extends TestCase
{
    // region METHOD_testPgSqlIdentifierEscaping [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): PgSql]
    /**
     * @purpose Verify PgSqlGrammar escapes identifiers with double quotes.
     */
    public function testPgSqlIdentifierEscaping(): void
    {
        $grammar = new PgSqlGrammar();
        self::assertSame('"table"', $grammar->escapeIdentifier('table'));
        self::assertSame('"col"', $grammar->escapeIdentifier('col'));
        self::assertSame('*', $grammar->escapeIdentifier('*'));
    }
    // endregion METHOD_testPgSqlIdentifierEscaping

    // region METHOD_testPgSqlEscapeDoubleQuote [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): QuoteEscape]
    /**
     * @purpose Verify PgSqlGrammar escapes double quotes inside identifiers.
     */
    public function testPgSqlEscapeDoubleQuote(): void
    {
        $grammar = new PgSqlGrammar();
        self::assertSame('"a""b"', $grammar->escapeIdentifier('a"b'));
    }
    // endregion METHOD_testPgSqlEscapeDoubleQuote

    // region METHOD_testPgSqlEscapeIdentifierTrimming [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): TrimQuotes]
    /**
     * @purpose Verify PgSqlGrammar trims existing double quotes from identifier.
     */
    public function testPgSqlEscapeIdentifierTrimming(): void
    {
        $grammar = new PgSqlGrammar();
        self::assertSame('"table"', $grammar->escapeIdentifier('"table"'));
    }
    // endregion METHOD_testPgSqlEscapeIdentifierTrimming

    // region METHOD_testMySqlIdentifierEscaping [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): MySql]
    /**
     * @purpose Verify MySqlGrammar escapes identifiers with backticks.
     */
    public function testMySqlIdentifierEscaping(): void
    {
        $grammar = new MySqlGrammar();
        self::assertSame('`table`', $grammar->escapeIdentifier('table'));
        self::assertSame('`col`', $grammar->escapeIdentifier('col'));
        self::assertSame('*', $grammar->escapeIdentifier('*'));
    }
    // endregion METHOD_testMySqlIdentifierEscaping

    // region METHOD_testMySqlEscapeBacktick [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): BacktickEscape]
    /**
     * @purpose Verify MySqlGrammar escapes backticks inside identifiers.
     */
    public function testMySqlEscapeBacktick(): void
    {
        $grammar = new MySqlGrammar();
        self::assertSame('`a``b`', $grammar->escapeIdentifier('a`b'));
    }
    // endregion METHOD_testMySqlEscapeBacktick

    // region METHOD_testMySqlEscapeIdentifierTrimming [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): TrimBackticks]
    /**
     * @purpose Verify MySqlGrammar trims existing backticks from identifier.
     */
    public function testMySqlEscapeIdentifierTrimming(): void
    {
        $grammar = new MySqlGrammar();
        self::assertSame('`table`', $grammar->escapeIdentifier('`table`'));
    }
    // endregion METHOD_testMySqlEscapeIdentifierTrimming

    // region METHOD_testDottedIdentifier [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): Dotted]
    /**
     * @purpose Verify escapeIdentifierDotted splits on dot and escapes each part.
     */
    public function testDottedIdentifier(): void
    {
        $grammar = new PgSqlGrammar();
        self::assertSame('"table"."column"', $grammar->escapeIdentifierDotted('table.column'));
        self::assertSame('"db"."schema"."table"', $grammar->escapeIdentifierDotted('db.schema.table'));
    }
    // endregion METHOD_testDottedIdentifier

    // region METHOD_testTableAliasSimple [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): SimpleAlias]
    /**
     * @purpose Verify escapeTableAlias escapes simple alias.
     */
    public function testTableAliasSimple(): void
    {
        $grammar = new PgSqlGrammar();
        self::assertSame('"u"', $grammar->escapeTableAlias('u'));
    }
    // endregion METHOD_testTableAliasSimple

    // region METHOD_testTableAliasWithColumns [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): ColumnList]
    /**
     * @purpose Verify escapeTableAlias escapes alias with column list.
     */
    public function testTableAliasWithColumns(): void
    {
        $grammar = new PgSqlGrammar();
        self::assertSame('"u"("id", "name")', $grammar->escapeTableAlias('u(id, name)'));
    }
    // endregion METHOD_testTableAliasWithColumns

    // region METHOD_testTableAliasDottedWithColumns [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): DottedAlias]
    /**
     * @purpose Verify escapeTableAlias handles dotted alias with columns.
     */
    public function testTableAliasDottedWithColumns(): void
    {
        $grammar = new PgSqlGrammar();
        self::assertSame('"s"."u"("id")', $grammar->escapeTableAlias('s.u(id)'));
    }
    // endregion METHOD_testTableAliasDottedWithColumns

    // region METHOD_testBuildMaybeReturnableSelect [DOMAIN(9): Testing; CONCEPT(9): Dispatch; TECH(9): Returnable]
    /**
     * @purpose Verify buildMaybeReturnableQuery dispatches to buildSelectQuery for SelectQueryInterface.
     */
    public function testBuildMaybeReturnableSelect(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);

        $built = $grammar->buildMaybeReturnableQuery($query);
        self::assertSame('SELECT "id" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildMaybeReturnableSelect

    // region METHOD_testBuildMaybeReturnableValues [DOMAIN(9): Testing; CONCEPT(9): Dispatch; TECH(9): Returnable]
    /**
     * @purpose Verify buildMaybeReturnableQuery dispatches to buildValuesQuery for ValuesQueryInterface.
     */
    public function testBuildMaybeReturnableValues(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new ValuesQuery();
        $query->values([[1]]);

        $built = $grammar->buildMaybeReturnableQuery($query);
        self::assertMatchesRegularExpression('/^VALUES \(:v\d+_\d+\)$/', $built->sql);
        self::assertCount(1, $built->params);
        self::assertContains(1, $built->params);
    }
    // endregion METHOD_testBuildMaybeReturnableValues

    // region METHOD_testBuildMaybeReturnableThrows [DOMAIN(9): Testing; CONCEPT(9): Dispatch; TECH(9): NotReturnable]
    /**
     * @purpose Verify buildMaybeReturnableQuery throws for non-returnable query (PgSqlDeleteQuery without RETURNING).
     */
    public function testBuildMaybeReturnableThrows(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new PgSqlDeleteQuery();
        $query->from(['users']);

        $this->expectException(QueryBuilderException::class);
        $grammar->buildMaybeReturnableQuery($query);
    }
    // endregion METHOD_testBuildMaybeReturnableThrows

    // region METHOD_testBuildMaybeReturnablePgSqlDeleteWithReturning [DOMAIN(9): Testing; CONCEPT(9): Grammar; TECH(9): Returnable]
    /**
     * @purpose Verify PgSqlGrammar::buildMaybeReturnableQuery succeeds for PgSqlDeleteQuery with RETURNING set.
     */
    public function testBuildMaybeReturnablePgSqlDeleteWithReturning(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlDeleteQuery();
        $query->from(['users'])->where(['active' => false]);
        $query->returning(['id']);

        $built = $grammar->buildMaybeReturnableQuery($query);
        self::assertStringContainsString('DELETE FROM "users"', $built->sql);
        self::assertStringContainsString('RETURNING "id"', $built->sql);
    }
    // endregion METHOD_testBuildMaybeReturnablePgSqlDeleteWithReturning

    // region METHOD_testBuildMaybeReturnablePgSqlInsertWithoutReturning [DOMAIN(9): Testing; CONCEPT(9): Grammar; TECH(9): Returnable]
    /**
     * @purpose Verify PgSqlGrammar::buildMaybeReturnableQuery throws for PgSqlInsertQuery without RETURNING.
     */
    public function testBuildMaybeReturnablePgSqlInsertWithoutReturning(): void
    {
        $grammar = new PgSqlGrammar();
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id']);

        $this->expectException(QueryBuilderException::class);
        $grammar->buildMaybeReturnableQuery($query);
    }
    // endregion METHOD_testBuildMaybeReturnablePgSqlInsertWithoutReturning

    // region METHOD_testBuildMaybeReturnablePgSqlInsertWithReturning [DOMAIN(9): Testing; CONCEPT(9): Grammar; TECH(9): Returnable]
    /**
     * @purpose Verify PgSqlGrammar::buildMaybeReturnableQuery for PgSqlInsertQuery with RETURNING — documents that INSERT is not yet routed through buildMaybeReturnableQuery.
     */
    public function testBuildMaybeReturnablePgSqlInsertWithReturning(): void
    {
        $grammar = new PgSqlGrammar();
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id']);
        $query->returning(['id']);

        self::assertTrue($query->isReturnable());
        $this->expectException(QueryBuilderException::class);
        $grammar->buildMaybeReturnableQuery($query);
    }
    // endregion METHOD_testBuildMaybeReturnablePgSqlInsertWithReturning

    // region METHOD_testAbstractGrammarBuildSelect [DOMAIN(9): Testing; CONCEPT(9): Grammar; TECH(9): BasicSelect]
    /**
     * @purpose Verify AbstractGrammar builds basic SELECT via anonymous class.
     */
    public function testAbstractGrammarBuildSelect(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new SelectQuery();
        $query->select(['id', 'name'])->from(['users'])->where(new \AndrewGos\QueryBuilder\Expr\Expr('"age" = 25'));

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id", "name" FROM "users" WHERE "age" = 25', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testAbstractGrammarBuildSelect
}
// endregion CLASS_GrammarTest
