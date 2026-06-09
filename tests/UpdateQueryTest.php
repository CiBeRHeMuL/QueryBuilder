<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\DefaultValue;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\Update\PgSql\PgSqlSetClause;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Update\MySql\MySqlUpdateQuery;
use AndrewGos\QueryBuilder\Query\Update\PgSql\PgSqlUpdateQuery;
use AndrewGos\QueryBuilder\Query\Update\UpdateQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_UpdateQueryTest [DOMAIN(9): Testing; CONCEPT(9): UpdateQuery; TECH(9): SQLGeneration]
/**
 * @purpose Test UPDATE query building across all dialects: AbstractGrammar, PgSql, MySql. Covers all SET variants.
 */
class UpdateQueryTest extends TestCase
{
    private AbstractGrammar $grammar;

    protected function setUp(): void
    {
        $this->grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
    }

    // region METHOD_testAbstractGrammarBasicUpdate [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): Basic]
    /**
     * @purpose Verify AbstractGrammar builds basic UPDATE with SET and WHERE.
     */
    public function testAbstractGrammarBasicUpdate(): void
    {
        $query = new UpdateQuery();
        $query->table('users')->set(['name' => 'Alice', 'age' => 30])->where(['id' => 1]);

        $built = $this->grammar->buildUpdateQuery($query);
        self::assertStringContainsString('UPDATE "users"', $built->sql);
        self::assertStringContainsString('SET', $built->sql);
        self::assertStringContainsString('WHERE', $built->sql);
        self::assertNotEmpty($built->params);
    }
    // endregion METHOD_testAbstractGrammarBasicUpdate

    // region METHOD_testAbstractGrammarUpdateWithCTE [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): WithCTE]
    /**
     * @purpose Verify AbstractGrammar builds UPDATE with WITH clause.
     */
    public function testAbstractGrammarUpdateWithCTE(): void
    {
        $cteQuery = new SelectQuery();
        $cteQuery->select(['id'])->from(['inactive_users']);

        $query = new UpdateQuery();
        $query->table('users')->set(['active' => false]);
        $query->with(['inactive' => new WithQuery($cteQuery)]);

        $built = $this->grammar->buildUpdateQuery($query);
        self::assertStringContainsString('WITH "inactive" AS ( SELECT "id" FROM "inactive_users" )', $built->sql);
        self::assertStringContainsString('UPDATE "users"', $built->sql);
        self::assertStringContainsString('SET', $built->sql);
    }
    // endregion METHOD_testAbstractGrammarUpdateWithCTE

    // region METHOD_testAbstractGrammarEmptyTableThrows [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): Validation]
    /**
     * @purpose Verify AbstractGrammar throws when UPDATE table is not set.
     */
    public function testAbstractGrammarEmptyTableThrows(): void
    {
        $query = new UpdateQuery();
        $query->set(['name' => 'Alice']);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('UPDATE query requires a table name');
        $this->grammar->buildUpdateQuery($query);
    }
    // endregion METHOD_testAbstractGrammarEmptyTableThrows

    // region METHOD_testAbstractGrammarUpdateNoSetThrows [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): NoSetValidation]
    /**
     * @purpose Verify AbstractGrammar throws when UPDATE has no SET clause.
     */
    public function testAbstractGrammarUpdateNoSetThrows(): void
    {
        $query = new UpdateQuery();
        $query->table('users')->where(['active' => false]);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('UPDATE query requires at least one SET clause');
        $this->grammar->buildUpdateQuery($query);
    }
    // endregion METHOD_testAbstractGrammarUpdateNoSetThrows

    // region METHOD_testAbstractGrammarUpdateDefaultValue [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): DefaultValue]
    /**
     * @purpose Verify AbstractGrammar builds UPDATE with DEFAULT value.
     */
    public function testAbstractGrammarUpdateDefaultValue(): void
    {
        $query = new UpdateQuery();
        $query->table('users')->set(['updated_at' => new DefaultValue()])->where(['id' => 1]);

        $built = $this->grammar->buildUpdateQuery($query);
        self::assertStringContainsString('"updated_at" = DEFAULT', $built->sql);
    }
    // endregion METHOD_testAbstractGrammarUpdateDefaultValue

    // region METHOD_testAbstractGrammarSetWithSetClauseObject [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): SetClauseObject]
    /**
     * @purpose Verify UpdateQuery::set() accepts SetClause objects directly.
     */
    public function testAbstractGrammarSetWithSetClauseObject(): void
    {
        $query = new UpdateQuery();
        $query->table('users');
        $query->set([new SetClause('name', 'Alice')]);

        $built = $this->grammar->buildUpdateQuery($query);
        self::assertCount(1, $query->set);
        self::assertInstanceOf(SetClause::class, $query->set[0]);
        self::assertStringContainsString('"name" =', $built->sql);
    }
    // endregion METHOD_testAbstractGrammarSetWithSetClauseObject

    // region METHOD_testAbstractGrammarMultiColumnSet [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): MultiColumn]
    /**
     * @purpose Verify AbstractGrammar builds multi-column SET: (c1, c2) = (v1, v2).
     */
    public function testAbstractGrammarMultiColumnSet(): void
    {
        $query = new UpdateQuery();
        $query->table('users');
        $query->set([new SetClause(['first_name', 'last_name'], ['Alice', 'Smith'])]);

        $built = $this->grammar->buildUpdateQuery($query);
        self::assertMatchesRegularExpression(
            '/^UPDATE "users" SET \("first_name", "last_name"\) = \([^)]+, [^)]+\)$/',
            $built->sql,
        );
        self::assertCount(2, $built->params);
    }
    // endregion METHOD_testAbstractGrammarMultiColumnSet

    // region METHOD_testAbstractGrammarMultiColumnSubquery [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): Subquery]
    /**
     * @purpose Verify AbstractGrammar builds multi-column SET with subquery.
     */
    public function testAbstractGrammarMultiColumnSubquery(): void
    {
        $subQuery = new SelectQuery();
        $subQuery->select(['first', 'last'])->from(['tmp'])->where(['id' => 1]);

        $query = new UpdateQuery();
        $query->table('users');
        $query->set([new SetClause(['first_name', 'last_name'], $subQuery)]);

        $built = $this->grammar->buildUpdateQuery($query);
        self::assertMatchesRegularExpression(
            '/^UPDATE "users" SET \("first_name", "last_name"\) = \(SELECT "first", "last" FROM "tmp" WHERE "id" = :v\d+_\d+\)$/',
            $built->sql,
        );
        self::assertCount(1, $built->params);
    }
    // endregion METHOD_testAbstractGrammarMultiColumnSubquery

    // region METHOD_testAbstractGrammarUpdateWhere [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): Where)
    /**
     * @purpose Verify AbstractGrammar builds UPDATE with WHERE conditions.
     */
    public function testAbstractGrammarUpdateWhere(): void
    {
        $query = new UpdateQuery();
        $query->table('users')->set(['status' => 'inactive'])->where(['active' => false]);

        $built = $this->grammar->buildUpdateQuery($query);
        self::assertStringContainsString('WHERE "active" IS FALSE', $built->sql);
    }
    // endregion METHOD_testAbstractGrammarUpdateWhere

    // region METHOD_testMySqlBasicUpdate [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): MySqlBasic]
    /**
     * @purpose Verify MySqlGrammar builds basic UPDATE with backtick escaping.
     */
    public function testMySqlBasicUpdate(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlUpdateQuery();
        $query->table('users')->set(['name' => 'Alice'])->where(['id' => 1]);

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('UPDATE `users`', $built->sql);
        self::assertStringContainsString('SET `name`', $built->sql);
        self::assertNotEmpty($built->params);
    }
    // endregion METHOD_testMySqlBasicUpdate

    // region METHOD_testMySqlModifiers [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): MySqlModifiers]
    /**
     * @purpose Verify MySqlGrammar builds UPDATE with LOW_PRIORITY and IGNORE.
     */
    public function testMySqlModifiers(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlUpdateQuery();
        $query->table('users')->set(['name' => 'Alice'])->where(['id' => 1]);
        $query->lowPriority()->ignore();

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('UPDATE LOW_PRIORITY IGNORE `users`', $built->sql);
    }
    // endregion METHOD_testMySqlModifiers

    // region METHOD_testMySqlMultiTable [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): MySqlMultiTable]
    /**
     * @purpose Verify MySqlGrammar builds multi-table UPDATE via addTable().
     */
    public function testMySqlMultiTable(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlUpdateQuery();
        $query->table('users')->addTable('profiles');
        $query->set(['users.name' => 'Alice'])->where(['users.id' => new Expr('profiles.user_id')]);

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('UPDATE `users`, `profiles`', $built->sql);
        self::assertCount(2, $query->tables);
    }
    // endregion METHOD_testMySqlMultiTable

    // region METHOD_testMySqlPartition [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): MySqlPartition]
    /**
     * @purpose Verify MySqlGrammar builds UPDATE with PARTITION clause.
     */
    public function testMySqlPartition(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlUpdateQuery();
        $query->table('users')->set(['name' => 'Alice']);
        $query->partition(['p1', 'p2']);

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('UPDATE `users`', $built->sql);
        self::assertStringContainsString('PARTITION (`p1`, `p2`)', $built->sql);
    }
    // endregion METHOD_testMySqlPartition

    // region METHOD_testMySqlOrderByLimit [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): MySqlOrderLimit]
    /**
     * @purpose Verify MySqlGrammar builds UPDATE with ORDER BY and LIMIT.
     */
    public function testMySqlOrderByLimit(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlUpdateQuery();
        $query->table('users')->set(['status' => 'inactive']);
        $query->orderBy(['id' => SORT_DESC]);
        $query->limit(10);

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('ORDER BY `id` DESC', $built->sql);
        self::assertMatchesRegularExpression('/LIMIT :v\d+_\d+, :v\d+_\d+/', $built->sql);
    }
    // endregion METHOD_testMySqlOrderByLimit

    // region METHOD_testMySqlAddTableSyncsTableProperty [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PropertySync]
    /**
     * @purpose Verify MySqlUpdateQuery $table getter returns first element of $tables.
     */
    public function testMySqlAddTableSyncsTableProperty(): void
    {
        $query = new MySqlUpdateQuery();
        $query->table('users');

        self::assertSame('users', $query->table);
        self::assertSame(['users'], $query->tables);

        $query->addTable('profiles');
        self::assertSame('users', $query->table);
        self::assertSame(['users', 'profiles'], $query->tables);
    }
    // endregion METHOD_testMySqlAddTableSyncsTableProperty

    // region METHOD_testMySqlEmptyTablesThrows [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): Validation]
    /**
     * @purpose Verify MySqlGrammar throws when UPDATE has no tables.
     */
    public function testMySqlEmptyTablesThrows(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlUpdateQuery();
        $query->set(['name' => 'Alice']);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('UPDATE query requires a table name');
        $grammar->buildUpdateQuery($query);
    }
    // endregion METHOD_testMySqlEmptyTablesThrows

    // region METHOD_testPgSqlBasicUpdate [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PgSqlBasic]
    /**
     * @purpose Verify PgSqlGrammar builds basic UPDATE with double-quote escaping.
     */
    public function testPgSqlBasicUpdate(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlUpdateQuery();
        $query->table('users')->set(['name' => 'Alice'])->where(['id' => 1]);

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('UPDATE "users"', $built->sql);
        self::assertStringContainsString('SET "name"', $built->sql);
        self::assertNotEmpty($built->params);
    }
    // endregion METHOD_testPgSqlBasicUpdate

    // region METHOD_testPgSqlUpdateFrom [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PgSqlFrom]
    /**
     * @purpose Verify PgSqlGrammar builds UPDATE with FROM (additional tables).
     */
    public function testPgSqlUpdateFrom(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlUpdateQuery();
        $query->table('users')->set(['users.name' => 'Alice']);
        $query->from(['profiles']);
        $query->where(['users.id' => new Expr('profiles.user_id')]);

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('UPDATE "users"', $built->sql);
        self::assertStringContainsString('FROM "profiles"', $built->sql);
        self::assertStringContainsString('WHERE', $built->sql);
    }
    // endregion METHOD_testPgSqlUpdateFrom

    // region METHOD_testPgSqlUpdateJoin [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PgSqlJoin]
    /**
     * @purpose Verify PgSqlGrammar builds UPDATE with JOIN.
     */
    public function testPgSqlUpdateJoin(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlUpdateQuery();
        $query->table('users')->set(['users.name' => 'Alice']);
        $query->innerJoin('profiles', ['users.id' => new Expr('profiles.user_id')]);
        $query->where(['users.active' => true]);

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('UPDATE "users"', $built->sql);
        self::assertStringContainsString('INNER JOIN', $built->sql);
        self::assertStringContainsString('WHERE', $built->sql);
    }
    // endregion METHOD_testPgSqlUpdateJoin

    // region METHOD_testPgSqlUpdateReturning [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PgSqlReturning]
    /**
     * @purpose Verify PgSqlGrammar builds UPDATE with RETURNING.
     */
    public function testPgSqlUpdateReturning(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlUpdateQuery();
        $query->table('users')->set(['name' => 'Alice'])->where(['id' => 1]);
        $query->returning(['id', 'name']);

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('UPDATE "users"', $built->sql);
        self::assertStringContainsString('RETURNING "id", "name"', $built->sql);
    }
    // endregion METHOD_testPgSqlUpdateReturning

    // region METHOD_testPgSqlUpdateFromJoinReturning [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): FullPipeline]
    /**
     * @purpose Verify PgSqlGrammar builds UPDATE with FROM + JOIN + RETURNING — full PgSql UPDATE pipeline.
     */
    public function testPgSqlUpdateFromJoinReturning(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlUpdateQuery();
        $query->table('users')->set(['users.name' => 'Alice']);
        $query->from(['profiles']);
        $query->innerJoin('settings', ['users.id' => new Expr('settings.user_id')]);
        $query->where(['users.active' => true]);
        $query->returning(['users.id']);

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('UPDATE "users"', $built->sql);
        self::assertStringContainsString('FROM "profiles"', $built->sql);
        self::assertStringContainsString('INNER JOIN', $built->sql);
        self::assertStringContainsString('WHERE', $built->sql);
        self::assertStringContainsString('RETURNING', $built->sql);
    }
    // endregion METHOD_testPgSqlUpdateFromJoinReturning

    // region METHOD_testPgSqlUpdateWithCTEAndReturning [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): CteReturning]
    /**
     * @purpose Verify PgSqlGrammar builds WITH + UPDATE ... RETURNING correctly.
     */
    public function testPgSqlUpdateWithCTEAndReturning(): void
    {
        $grammar = new PgSqlGrammar();

        $cteQuery = new SelectQuery();
        $cteQuery->select(['id'])->from(['inactive_users']);

        $query = new PgSqlUpdateQuery();
        $query->table('users')->set(['active' => false]);
        $query->with(['inactive' => new WithQuery($cteQuery)]);
        $query->returning(['id']);

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('WITH "inactive" AS', $built->sql);
        self::assertStringContainsString('UPDATE "users"', $built->sql);
        self::assertStringContainsString('RETURNING "id"', $built->sql);
    }
    // endregion METHOD_testPgSqlUpdateWithCTEAndReturning

    // region METHOD_testSetClauseMultiColumnNoRow [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): SetClauseMulti)
    /**
     * @purpose Verify base SetClause::getSql renders multi-column without ROW: (c1, c2) = (v1, v2).
     */
    public function testSetClauseMultiColumnNoRow(): void
    {
        $grammar = new PgSqlGrammar();
        $clause = new SetClause(['first_name', 'last_name'], ['Alice', 'Smith']);
        $expr = $clause->getSql($grammar);

        self::assertMatchesRegularExpression(
            '/^\("first_name", "last_name"\) = \(:v\d+_\d+, :v\d+_\d+\)$/',
            $expr->getExpression($grammar),
        );
        self::assertCount(2, $expr->getParams());
    }
    // endregion METHOD_testSetClauseMultiColumnNoRow

    // region METHOD_testPgSqlSetClauseRowDirect [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PgSqlRowDirect)
    /**
     * @purpose Verify PgSqlSetClause::getSql renders ROW syntax directly: (c1, c2) = ROW(v1, v2).
     */
    public function testPgSqlSetClauseRowDirect(): void
    {
        $grammar = new PgSqlGrammar();
        $clause = new PgSqlSetClause(['first_name', 'last_name'], ['Alice', 'Smith'], isRow: true);
        $expr = $clause->getSql($grammar);

        $sql = $expr->getExpression($grammar);
        self::assertStringStartsWith('("first_name", "last_name") = ROW(', $sql);
        self::assertStringEndsWith(')', $sql);
        self::assertMatchesRegularExpression(
            '/^ROW\(:v\d+_\d+, :v\d+_\d+\)$/',
            substr($sql, strpos($sql, 'ROW(')),
        );
        self::assertCount(2, $expr->getParams());
    }
    // endregion METHOD_testPgSqlSetClauseRowDirect

    // region METHOD_testPgSqlSetClauseRowFalse [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PgSqlRowFalse)
    /**
     * @purpose Verify PgSqlSetClause with isRow=false falls through to parent: no ROW keyword.
     */
    public function testPgSqlSetClauseRowFalse(): void
    {
        $grammar = new PgSqlGrammar();
        $clause = new PgSqlSetClause(['first_name', 'last_name'], ['Alice', 'Smith'], isRow: false);
        $expr = $clause->getSql($grammar);

        self::assertMatchesRegularExpression(
            '/^\("first_name", "last_name"\) = \(:v\d+_\d+, :v\d+_\d+\)$/',
            $expr->getExpression($grammar),
        );
        self::assertStringNotContainsString('ROW', $expr->getExpression($grammar));
    }
    // endregion METHOD_testPgSqlSetClauseRowFalse

    // region METHOD_testPgSqlSetClauseRowScalarValue [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PgSqlRowScalar)
    /**
     * @purpose Verify PgSqlSetClause with scalar (non-array) value still wraps in ROW(...) parens.
     */
    public function testPgSqlSetClauseRowScalarValue(): void
    {
        $grammar = new PgSqlGrammar();
        $clause = new PgSqlSetClause(['name'], 'Andrew', isRow: true);
        $expr = $clause->getSql($grammar);

        $sql = $expr->getExpression($grammar);
        self::assertStringContainsString('("name") = ROW(', $sql);
        self::assertStringEndsWith(')', $sql);
        self::assertCount(1, $expr->getParams());
    }
    // endregion METHOD_testPgSqlSetClauseRowScalarValue

    // region METHOD_testPgSqlSetClauseRowIntegration [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PgSqlRowIntegration)
    /**
     * @purpose Verify PgSqlGrammar builds UPDATE with ROW syntax end-to-end.
     */
    public function testPgSqlSetClauseRowIntegration(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlUpdateQuery();
        $query->table('users');
        $query->set([new PgSqlSetClause(['first_name', 'last_name'], ['Alice', 'Smith'], isRow: true)]);

        $built = $grammar->buildUpdateQuery($query);
        $rowPos = strpos($built->sql, 'ROW(');
        self::assertNotFalse($rowPos, 'ROW( must be present in SQL');
        self::assertStringEndsWith(')', substr($built->sql, $rowPos), 'ROW( must close with paren');
        self::assertCount(2, $built->params);
    }
    // endregion METHOD_testPgSqlSetClauseRowIntegration

    // region METHOD_testPgSqlUpdateReturningWithAliases [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PgSqlReturningAlias)
    /**
     * @purpose Verify PgSqlGrammar builds UPDATE with RETURNING OLD AS / NEW AS aliases.
     */
    public function testPgSqlUpdateReturningWithAliases(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlUpdateQuery();
        $query->table('users')->set(['name' => 'Alice'])->where(['id' => 1]);
        $query->returning(['id', 'name'], 'old', 'new');

        $built = $grammar->buildUpdateQuery($query);
        self::assertStringContainsString('RETURNING WITH (OLD AS "old", NEW AS "new")', $built->sql);
    }
    // endregion METHOD_testPgSqlUpdateReturningWithAliases

    // region METHOD_testPgSqlIsReturnable [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PgSqlReturnable]
    /**
     * @purpose Verify PgSqlUpdateQuery isReturnable only when RETURNING is set.
     */
    public function testPgSqlIsReturnable(): void
    {
        $query = new PgSqlUpdateQuery();
        $query->table('users')->set(['name' => 'Alice']);
        self::assertFalse($query->isReturnable());

        $query->returning(['id']);
        self::assertTrue($query->isReturnable());
    }
    // endregion METHOD_testPgSqlIsReturnable

    // region METHOD_testUpdateQueryPropertyHooks [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): PropertyHooks]
    /**
     * @purpose Verify UpdateQuery $table and $set property hooks work correctly.
     */
    public function testUpdateQueryPropertyHooks(): void
    {
        $query = new UpdateQuery();
        $query->table('users');
        $query->set(['name' => 'Alice']);

        self::assertSame('users', $query->table);
        self::assertCount(1, $query->set);
        self::assertInstanceOf(SetClause::class, $query->set[0]);
        self::assertSame('name', $query->set[0]->target);
    }
    // endregion METHOD_testUpdateQueryPropertyHooks

    // region METHOD_testMySqlBaseClassPropertyHooks [DOMAIN(9): Testing; CONCEPT(9): Update; TECH(9): MySqlPropertyHooks]
    /**
     * @purpose Verify MySqlUpdateQuery inherits table() and set() from UpdateQuery.
     */
    public function testMySqlBaseClassPropertyHooks(): void
    {
        $query = new MySqlUpdateQuery();
        $query->table('users');
        $query->set(['name' => 'Alice']);

        self::assertSame('users', $query->table);
        self::assertSame(['users'], $query->tables);
        self::assertCount(1, $query->set);
    }
    // endregion METHOD_testMySqlBaseClassPropertyHooks
}
// endregion CLASS_UpdateQueryTest
