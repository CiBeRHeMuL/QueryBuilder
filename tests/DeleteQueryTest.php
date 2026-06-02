<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Query\Delete\DeleteQuery;
use AndrewGos\QueryBuilder\Query\Delete\MySql\MySqlDeleteQuery;
use AndrewGos\QueryBuilder\Query\Delete\PgSql\PgSqlDeleteQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_DeleteQueryTest [DOMAIN(9): Testing; CONCEPT(9): DeleteQuery; TECH(9): SQLGeneration]
/**
 * @purpose Test DELETE query building across all dialects: AbstractGrammar, PgSql, MySql.
 */
class DeleteQueryTest extends TestCase
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

    // region METHOD_testAbstractGrammarBasicDelete [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): Basic]
    /**
     * @purpose Verify AbstractGrammar builds basic DELETE FROM with WHERE.
     */
    public function testAbstractGrammarBasicDelete(): void
    {
        $query = new DeleteQuery();
        $query->from(['users'])->where(['active' => false]);

        $built = $this->grammar->buildDeleteQuery($query);
        self::assertSame('DELETE FROM "users" WHERE ("active") IS FALSE', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testAbstractGrammarBasicDelete

    // region METHOD_testAbstractGrammarDeleteWithCTE [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): WithCTE]
    /**
     * @purpose Verify AbstractGrammar builds DELETE with WITH clause.
     */
    public function testAbstractGrammarDeleteWithCTE(): void
    {
        $cteQuery = new \AndrewGos\QueryBuilder\Query\Select\SelectQuery();
        $cteQuery->select(['id'])->from(['inactive_users']);

        $query = new DeleteQuery();
        $query->from(['users'])->where(['id' => new \AndrewGos\QueryBuilder\Expr\Expr('> 100')]);
        $query->with(['inactive' => new \AndrewGos\QueryBuilder\Expr\Cte\WithQuery($cteQuery)]);

        $built = $this->grammar->buildDeleteQuery($query);
        self::assertSame('WITH "inactive" AS ( SELECT "id" FROM "inactive_users" ) DELETE FROM "users" WHERE ("id") = (> 100)', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testAbstractGrammarDeleteWithCTE

    // region METHOD_testPgSqlUsingClause [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): PgSqlUsing]
    /**
     * @purpose Verify PgSqlGrammar builds DELETE with USING clause.
     */
    public function testPgSqlUsingClause(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlDeleteQuery();
        $query->from(['users'])->using(['deleted_log'])->where(['users.id' => new \AndrewGos\QueryBuilder\Expr\Expr('deleted_log.user_id')]);

        $built = $grammar->buildDeleteQuery($query);
        self::assertSame('DELETE FROM "users" USING "deleted_log" WHERE ("users"."id") = (deleted_log.user_id)', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlUsingClause

    // region METHOD_testPgSqlReturning [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): PgSqlReturning]
    /**
     * @purpose Verify PgSqlGrammar builds DELETE with RETURNING.
     */
    public function testPgSqlReturning(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlDeleteQuery();
        $query->from(['users'])->where(['active' => false]);
        $query->returning(['id']);

        $built = $grammar->buildDeleteQuery($query);
        self::assertSame('DELETE FROM "users" WHERE ("active") IS FALSE RETURNING "id"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlReturning

    // region METHOD_testPgSqlDeleteWithJoin [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): PgSqlJoin]
    /**
     * @purpose Verify PgSqlGrammar builds DELETE with USING + JOIN.
     */
    public function testPgSqlDeleteWithJoin(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlDeleteQuery();
        $query->from(['users'])->using(['deleted_log']);
        $query->innerJoin('profiles', ['users.id' => new \AndrewGos\QueryBuilder\Expr\Expr('profiles.user_id')]);

        $built = $grammar->buildDeleteQuery($query);
        self::assertStringContainsString('USING', $built->sql);
        self::assertStringContainsString('INNER JOIN', $built->sql);
        // Table name with alias embedded, no params
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlDeleteWithJoin

    // region METHOD_testPgSqlReturningWithAliases [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): PgSqlReturningAlias]
    /**
     * @purpose Verify PgSqlGrammar builds DELETE with RETURNING OLD AS / NEW AS aliases.
     */
    public function testPgSqlReturningWithAliases(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlDeleteQuery();
        $query->from(['users'])->where(['id' => 1]);
        $query->returning(['id', 'name'], 'old', 'new');

        $built = $grammar->buildDeleteQuery($query);
        self::assertMatchesRegularExpression(
            '/^DELETE FROM "users" WHERE \("id"\) = :v\d+_\d+ RETURNING WITH \(OLD AS "old", NEW AS "new"\)\s+"id", "name"$/',
            $built->sql,
        );
        self::assertCount(1, $built->params);
        self::assertContains(1, $built->params);
    }
    // endregion METHOD_testPgSqlReturningWithAliases

    // region METHOD_testPgSqlIsReturnable [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): PgSqlReturnable]
    /**
     * @purpose Verify PgSqlDeleteQuery isReturnable only when RETURNING is set.
     */
    public function testPgSqlIsReturnable(): void
    {
        $query = new PgSqlDeleteQuery();
        self::assertFalse($query->isReturnable());

        $query->returning(['id']);
        self::assertTrue($query->isReturnable());
    }
    // endregion METHOD_testPgSqlIsReturnable

    // region METHOD_testMySqlModifiers [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): MySqlModifiers]
    /**
     * @purpose Verify MySqlGrammar builds DELETE with LOW_PRIORITY, QUICK, IGNORE.
     */
    public function testMySqlModifiers(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlDeleteQuery();
        $query->from(['users'])->where(['id' => 1]);
        $query->lowPriority()->quick()->ignore();

        $built = $grammar->buildDeleteQuery($query);
        self::assertStringContainsString('DELETE LOW_PRIORITY QUICK IGNORE', $built->sql);
        self::assertStringContainsString('FROM `users`', $built->sql);
        // `id` IS 1 produces a param - verify the param value
        self::assertCount(1, $built->params);
        self::assertContains(1, $built->params);
    }
    // endregion METHOD_testMySqlModifiers

    // region METHOD_testMySqlDeletePartition [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): MySqlPartition]
    /**
     * @purpose Verify MySqlGrammar builds DELETE with PARTITION clause.
     */
    public function testMySqlDeletePartition(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlDeleteQuery();
        $query->from(['users'])->partition(['p1']);

        $built = $grammar->buildDeleteQuery($query);
        self::assertSame('DELETE FROM `users` PARTITION (`p1`)', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testMySqlDeletePartition

    // region METHOD_testMySqlDeleteOrderByLimit [DOMAIN(9): Testing; CONCEPT(9): Delete; TECH(9): MySqlOrderLimit]
    /**
     * @purpose Verify MySqlDeleteQuery accepts OrderBy and Limit trait methods without error.
     */
    public function testMySqlDeleteOrderByLimit(): void
    {
        $query = new MySqlDeleteQuery();
        $query->from(['users']);
        $query->orderBy(['id' => SORT_DESC]);
        $query->limit(10);

        self::assertCount(1, $query->orderBy);
        self::assertSame(10, $query->limit);
    }
    // endregion METHOD_testMySqlDeleteOrderByLimit
}
// endregion CLASS_DeleteQueryTest
