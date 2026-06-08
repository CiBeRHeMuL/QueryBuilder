<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Grammar\Default\DefaultGrammar;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Query\Delete\DeleteQuery;
use AndrewGos\QueryBuilder\Query\Delete\PgSql\PgSqlDeleteQuery;
use AndrewGos\QueryBuilder\Query\Insert\InsertQuery;
use AndrewGos\QueryBuilder\Query\Insert\PgSql\PgSqlInsertQuery;
use AndrewGos\QueryBuilder\Query\Select\PgSql\PgSqlSelectQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Update\UpdateQuery;
use AndrewGos\QueryBuilder\Query\Values\ValuesQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_DefaultGrammarTest [DOMAIN(9): Testing; CONCEPT(9): DefaultGrammar; TECH(9): ANSI_SQL]
/**
 * @purpose Test DefaultGrammar — ANSI SQL identifier escaping, basic query building, and vendor-specific feature passthrough.
 */
class DefaultGrammarTest extends TestCase
{
    private DefaultGrammar $grammar;

    protected function setUp(): void
    {
        $this->grammar = new DefaultGrammar();
    }

    // region METHOD_testEscapeIdentifier [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): Basic]
    /**
     * @purpose Verify DefaultGrammar escapes identifiers with double quotes per ANSI SQL standard.
     */
    public function testEscapeIdentifier(): void
    {
        self::assertSame('"table"', $this->grammar->escapeIdentifier('table'));
        self::assertSame('"column_name"', $this->grammar->escapeIdentifier('column_name'));
        self::assertSame('*', $this->grammar->escapeIdentifier('*'));
    }
    // endregion METHOD_testEscapeIdentifier

    // region METHOD_testEscapeDoubleQuote [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): QuoteEscape]
    /**
     * @purpose Verify DefaultGrammar escapes double quotes inside identifiers via doubling.
     */
    public function testEscapeDoubleQuote(): void
    {
        self::assertSame('"a""b"', $this->grammar->escapeIdentifier('a"b'));
    }
    // endregion METHOD_testEscapeDoubleQuote

    // region METHOD_testEscapeIdentifierTrimming [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): TrimQuotes]
    /**
     * @purpose Verify DefaultGrammar trims existing double quotes and whitespace from identifier.
     */
    public function testEscapeIdentifierTrimming(): void
    {
        self::assertSame('"table"', $this->grammar->escapeIdentifier('"table"'));
        self::assertSame('"table"', $this->grammar->escapeIdentifier('  "table"  '));
        self::assertSame('"tab le"', $this->grammar->escapeIdentifier('"tab le"'));
        self::assertSame('"table"', $this->grammar->escapeIdentifier("\"\ntable\n\""));
    }
    // endregion METHOD_testEscapeIdentifierTrimming

    // region METHOD_testEscapeIdentifierDotted [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): Dotted]
    /**
     * @purpose Verify escapeIdentifierDotted splits on dot and escapes each part.
     */
    public function testEscapeIdentifierDotted(): void
    {
        self::assertSame('"table"."column"', $this->grammar->escapeIdentifierDotted('table.column'));
        self::assertSame('"db"."schema"."table"', $this->grammar->escapeIdentifierDotted('db.schema.table'));
    }
    // endregion METHOD_testEscapeIdentifierDotted

    // region METHOD_testEscapeTableAliasSimple [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): SimpleAlias]
    /**
     * @purpose Verify escapeTableAlias escapes simple alias.
     */
    public function testEscapeTableAliasSimple(): void
    {
        self::assertSame('"u"', $this->grammar->escapeTableAlias('u'));
    }
    // endregion METHOD_testEscapeTableAliasSimple

    // region METHOD_testEscapeTableAliasWithColumns [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): ColumnList]
    /**
     * @purpose Verify escapeTableAlias escapes alias with column list.
     */
    public function testEscapeTableAliasWithColumns(): void
    {
        self::assertSame('"u"("id", "name")', $this->grammar->escapeTableAlias('u(id, name)'));
    }
    // endregion METHOD_testEscapeTableAliasWithColumns

    // region METHOD_testEscapeTableAliasDottedWithColumns [DOMAIN(9): Testing; CONCEPT(9): Escaping; TECH(9): DottedAlias]
    /**
     * @purpose Verify escapeTableAlias handles dotted alias with columns.
     */
    public function testEscapeTableAliasDottedWithColumns(): void
    {
        self::assertSame('"s"."u"("id")', $this->grammar->escapeTableAlias('s.u(id)'));
    }
    // endregion METHOD_testEscapeTableAliasDottedWithColumns

    // region METHOD_testBuildSelectBasic [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Basic]
    /**
     * @purpose Verify DefaultGrammar builds a basic SELECT query.
     */
    public function testBuildSelectBasic(): void
    {
        $query = new SelectQuery();
        $query->select(['id', 'name'])->from(['users']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id", "name" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildSelectBasic

    // region METHOD_testBuildSelectWithWhere [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Where]
    /**
     * @purpose Verify DefaultGrammar builds SELECT with WHERE clause.
     */
    public function testBuildSelectWithWhere(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users'])->where(new Expr('"active" = 1'));

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" WHERE "active" = 1', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildSelectWithWhere

    // region METHOD_testBuildSelectWithJoin [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Join]
    /**
     * @purpose Verify DefaultGrammar builds SELECT with JOIN clause.
     */
    public function testBuildSelectWithJoin(): void
    {
        $query = new SelectQuery();
        $query->select(['u.id', 'p.name'])
            ->from(['users u'])
            ->innerJoin('profiles p', ['u.id' => 'p.user_id']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "u"."id", "p"."name" FROM "users u" INNER JOIN "profiles p" ON "u"."id" = "p"."user_id"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildSelectWithJoin

    // region METHOD_testBuildSelectWithOrderByAndLimit [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): OrderLimit]
    /**
     * @purpose Verify DefaultGrammar builds SELECT with ORDER BY and LIMIT.
     */
    public function testBuildSelectWithOrderByAndLimit(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users'])->orderBy(['name' => SORT_ASC])->limit(10);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" ORDER BY "name" ASC FETCH FIRST 10 ROWS ONLY', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildSelectWithOrderByAndLimit

    // region METHOD_testBuildSelectWithGroupByAndHaving [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): GroupByHaving]
    /**
     * @purpose Verify DefaultGrammar builds SELECT with GROUP BY and HAVING.
     */
    public function testBuildSelectWithGroupByAndHaving(): void
    {
        $query = new SelectQuery();
        $query->select(['type', new Expr('COUNT(*)')])
            ->from(['items'])
            ->groupBy(['type'])
            ->having(['COUNT(*)' => new Expr('> 1')]);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "type", COUNT(*) FROM "items" GROUP BY "type" HAVING "COUNT(*)" = (> 1)', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildSelectWithGroupByAndHaving

    // region METHOD_testBuildSelectWithCte [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): CTE]
    /**
     * @purpose Verify DefaultGrammar builds SELECT with CTE.
     */
    public function testBuildSelectWithCte(): void
    {
        $inner = new SelectQuery();
        $inner->select(['id'])->from(['users']);

        $query = new SelectQuery();
        $query->with(['active_users' => new WithQuery($inner)])
            ->select(['id'])
            ->from(['active_users']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('WITH "active_users" AS ( SELECT "id" FROM "users" ) SELECT "id" FROM "active_users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildSelectWithCte

    // region METHOD_testBuildSelectWithSetOperation [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): SetOp]
    /**
     * @purpose Verify DefaultGrammar builds SELECT with UNION ALL set operation.
     */
    public function testBuildSelectWithSetOperation(): void
    {
        $q2 = new SelectQuery();
        $q2->select(['name'])->from(['admins']);

        $query = new SelectQuery();
        $query->select(['name'])->from(['users'])->unionAll($q2);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "name" FROM "users" UNION ALL (SELECT "name" FROM "admins")', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildSelectWithSetOperation

    // region METHOD_testBuildDeleteBasic [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): Basic]
    /**
     * @purpose Verify DefaultGrammar builds a basic DELETE query.
     */
    public function testBuildDeleteBasic(): void
    {
        $query = new DeleteQuery();
        $query->from(['users'])->where(new Expr('"active" = 0'));

        $built = $this->grammar->buildDeleteQuery($query);
        self::assertSame('DELETE FROM "users" WHERE "active" = 0', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildDeleteBasic

    // region METHOD_testBuildDeleteWithCte [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): CTE]
    /**
     * @purpose Verify DefaultGrammar builds DELETE with CTE.
     */
    public function testBuildDeleteWithCte(): void
    {
        $inner = new SelectQuery();
        $inner->select(['id'])->from(['inactive_users']);

        $query = new DeleteQuery();
        $query->with(['to_delete' => new WithQuery($inner)])
            ->from(['users'])
            ->where(['id' => new Expr('(SELECT "id" FROM "to_delete")')]);

        $built = $this->grammar->buildDeleteQuery($query);
        self::assertSame('WITH "to_delete" AS ( SELECT "id" FROM "inactive_users" ) DELETE FROM "users" WHERE "id" = ((SELECT "id" FROM "to_delete"))', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildDeleteWithCte

    // region METHOD_testBuildInsertDefaultValues [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): DefaultValues]
    /**
     * @purpose Verify DefaultGrammar builds INSERT DEFAULT VALUES.
     */
    public function testBuildInsertDefaultValues(): void
    {
        $query = new InsertQuery();
        $query->into('users');

        $built = $this->grammar->buildInsertQuery($query);
        self::assertSame('INSERT INTO "users" DEFAULT VALUES', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildInsertDefaultValues

    // region METHOD_testBuildInsertValues [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): ValuesSource]
    /**
     * @purpose Verify DefaultGrammar builds INSERT with VALUES source.
     */
    public function testBuildInsertValues(): void
    {
        $values = new ValuesQuery();
        $values->values([[1, 'test']]);

        $query = new InsertQuery();
        $query->into('users', ['id', 'name'])->source($values);

        $built = $this->grammar->buildInsertQuery($query);
        self::assertMatchesRegularExpression('/^INSERT INTO "users" \("id", "name"\) VALUES \(:v\d+_\d+, :v\d+_\d+\)$/', $built->sql);
        self::assertCount(2, $built->params);
    }
    // endregion METHOD_testBuildInsertValues

    // region METHOD_testBuildInsertSelect [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): SelectSource]
    /**
     * @purpose Verify DefaultGrammar builds INSERT with SELECT source.
     */
    public function testBuildInsertSelect(): void
    {
        $source = new SelectQuery();
        $source->select(['id', 'name'])->from(['import_users']);

        $query = new InsertQuery();
        $query->into('users', ['id', 'name'])->source($source);

        $built = $this->grammar->buildInsertQuery($query);
        self::assertSame('INSERT INTO "users" ("id", "name") (SELECT "id", "name" FROM "import_users")', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildInsertSelect

    // region METHOD_testBuildValuesBasic [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): SingleRow]
    /**
     * @purpose Verify DefaultGrammar builds VALUES with a single row.
     */
    public function testBuildValuesBasic(): void
    {
        $query = new ValuesQuery();
        $query->values([[1, 'test']]);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertMatchesRegularExpression('/^VALUES \(:v\d+_\d+, :v\d+_\d+\)$/', $built->sql);
        self::assertCount(2, $built->params);
    }
    // endregion METHOD_testBuildValuesBasic

    // region METHOD_testBuildValuesMultipleRows [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): MultiRow]
    /**
     * @purpose Verify DefaultGrammar builds VALUES with multiple rows.
     */
    public function testBuildValuesMultipleRows(): void
    {
        $query = new ValuesQuery();
        $query->values([[1], [2], [3]]);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertMatchesRegularExpression('/^VALUES \(:v\d+_\d+\), \(:v\d+_\d+\), \(:v\d+_\d+\)$/', $built->sql);
        self::assertCount(3, $built->params);
    }
    // endregion METHOD_testBuildValuesMultipleRows

    // region METHOD_testBuildValuesWithOrderAndLimit [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): OrderLimit]
    /**
     * @purpose Verify DefaultGrammar builds VALUES with ORDER BY and LIMIT.
     */
    public function testBuildValuesWithOrderAndLimit(): void
    {
        $query = new ValuesQuery();
        $query->values([[1], [2]]);
        $query->orderBy(['1']);
        $query->limit(1);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertMatchesRegularExpression('/^VALUES \(:v\d+_\d+\), \(:v\d+_\d+\) ORDER BY "1" ASC FETCH FIRST 1 ROW ONLY$/', $built->sql);
        self::assertCount(2, $built->params);
    }
    // endregion METHOD_testBuildValuesWithOrderAndLimit

    // region METHOD_testBuildUpdateQueryThrows [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): Validation]
    /**
     * @purpose Verify DefaultGrammar::buildUpdateQuery throws QueryBuilderException when table is empty.
     */
    public function testBuildUpdateQueryThrows(): void
    {
        $query = new UpdateQuery();
        $query->set(['name' => 'test']);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('UPDATE query requires a table name.');
        $this->grammar->buildUpdateQuery($query);
    }
    // endregion METHOD_testBuildUpdateQueryThrows

    // region METHOD_testBuildMaybeReturnableSelect [DOMAIN(9): Testing; CONCEPT(9): Returnable; TECH(9): Select]
    /**
     * @purpose Verify buildMaybeReturnableQuery dispatches to buildSelectQuery for SelectQuery.
     */
    public function testBuildMaybeReturnableSelect(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);

        $built = $this->grammar->buildMaybeReturnableQuery($query);
        self::assertSame('SELECT "id" FROM "users"', $built->sql);
    }
    // endregion METHOD_testBuildMaybeReturnableSelect

    // region METHOD_testBuildMaybeReturnableValues [DOMAIN(9): Testing; CONCEPT(9): Returnable; TECH(9): Values]
    /**
     * @purpose Verify buildMaybeReturnableQuery dispatches to buildValuesQuery for ValuesQuery.
     */
    public function testBuildMaybeReturnableValues(): void
    {
        $query = new ValuesQuery();
        $query->values([[1]]);

        $built = $this->grammar->buildMaybeReturnableQuery($query);
        self::assertMatchesRegularExpression('/^VALUES \(:v\d+_\d+\)$/', $built->sql);
    }
    // endregion METHOD_testBuildMaybeReturnableValues

    // region METHOD_testBuildMaybeReturnableNonReturnableThrows [DOMAIN(9): Testing; CONCEPT(9): Returnable; TECH(9): NonReturnable]
    /**
     * @purpose Verify buildMaybeReturnableQuery throws for PgSqlDeleteQuery without RETURNING (isReturnable() === false).
     */
    public function testBuildMaybeReturnableNonReturnableThrows(): void
    {
        $query = new PgSqlDeleteQuery();
        $query->from(['users']);

        $this->expectException(QueryBuilderException::class);
        $this->grammar->buildMaybeReturnableQuery($query);
    }
    // endregion METHOD_testBuildMaybeReturnableNonReturnableThrows

    // region METHOD_testBuildMaybeReturnableUnhandledReturnableThrows [DOMAIN(9): Testing; CONCEPT(9): Returnable; TECH(9): UnhandledType]
    /**
     * @purpose Verify buildMaybeReturnableQuery throws for a returnable query that AbstractGrammar cannot dispatch (PgSqlDeleteQuery with RETURNING — isReturnable=true, not SelectQueryInterface or ValuesQueryInterface).
     */
    public function testBuildMaybeReturnableUnhandledReturnableThrows(): void
    {
        $query = new PgSqlDeleteQuery();
        $query->from(['users']);
        $query->returning(['id']);

        self::assertTrue($query->isReturnable());
        $this->expectException(QueryBuilderException::class);
        $this->grammar->buildMaybeReturnableQuery($query);
    }
    // endregion METHOD_testBuildMaybeReturnableUnhandledReturnableThrows

    // region METHOD_testPgSqlSelectQueryFeaturesIgnored [DOMAIN(9): Testing; CONCEPT(9): VendorPassthrough; TECH(9): PgSqlSelect]
    /**
     * @purpose Verify DefaultGrammar ignores PgSql-specific query properties and builds standard SELECT.
     */
    public function testPgSqlSelectQueryFeaturesIgnored(): void
    {
        $query = new PgSqlSelectQuery();
        $query->select(['id'])->from(['users']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users"', $built->sql);
    }
    // endregion METHOD_testPgSqlSelectQueryFeaturesIgnored

    // region METHOD_testPgSqlDeleteQueryWorksWithoutReturning [DOMAIN(9): Testing; CONCEPT(9): VendorPassthrough; TECH(9): PgSqlDelete]
    /**
     * @purpose Verify DefaultGrammar builds DELETE from PgSqlDeleteQuery ignoring PgSql-specific USING/RETURNING features.
     */
    public function testPgSqlDeleteQueryWorksWithoutReturning(): void
    {
        $query = new PgSqlDeleteQuery();
        $query->from(['users'])->where(new Expr('"id" = 1'));

        $built = $this->grammar->buildDeleteQuery($query);
        self::assertSame('DELETE FROM "users" WHERE "id" = 1', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlDeleteQueryWorksWithoutReturning

    // region METHOD_testPgSqlInsertQueryWorksWithoutPgSqlFeatures [DOMAIN(9): Testing; CONCEPT(9): VendorPassthrough; TECH(9): PgSqlInsert]
    /**
     * @purpose Verify DefaultGrammar builds INSERT from PgSqlInsertQuery ignoring PgSql-specific features.
     */
    public function testPgSqlInsertQueryWorksWithoutPgSqlFeatures(): void
    {
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id']);

        $built = $this->grammar->buildInsertQuery($query);
        self::assertSame('INSERT INTO "users" ("id") DEFAULT VALUES', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlInsertQueryWorksWithoutPgSqlFeatures

    // region METHOD_testBuildSelectWithMultipleCtes [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): CTE]
    /**
     * @purpose Verify DefaultGrammar builds SELECT with multiple CTEs separated by comma.
     */
    public function testBuildSelectWithMultipleCtes(): void
    {
        $cte1 = new SelectQuery();
        $cte1->select(['id'])->from(['users']);

        $cte2 = new SelectQuery();
        $cte2->select(['name'])->from(['roles']);

        $query = new SelectQuery();
        $query->with(['active_users' => new WithQuery($cte1)])
            ->addWith(['role_names' => new WithQuery($cte2)])
            ->select(['id', 'name'])
            ->from(['active_users'])
            ->innerJoin('role_names', ['active_users.role_id' => 'role_names.id']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame(
            'WITH "active_users" AS ( SELECT "id" FROM "users" ), "role_names" AS ( SELECT "name" FROM "roles" ) SELECT "id", "name" FROM "active_users" INNER JOIN "role_names" ON "active_users"."role_id" = "role_names"."id"',
            $built->sql,
        );
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBuildSelectWithMultipleCtes

    // region METHOD_testGrammarInterfaceCompliance [DOMAIN(9): Testing; CONCEPT(9): Contract; TECH(9): Interface]
    /**
     * @purpose Verify DefaultGrammar implements GrammarInterface correctly.
     */
    public function testGrammarInterfaceCompliance(): void
    {
        self::assertInstanceOf(GrammarInterface::class, $this->grammar);
    }
    // endregion METHOD_testGrammarInterfaceCompliance
}
// endregion CLASS_DefaultGrammarTest
