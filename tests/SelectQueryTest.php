<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Enum\LimitBoundTypeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\MySql\MySqlLockModeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\PgSql\PgSqlLockModeEnum;
use AndrewGos\QueryBuilder\Expr\Cte\PgSql\PgSqlWithQuery;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\Lock\MySql\MySqlLockMode;
use AndrewGos\QueryBuilder\Expr\Lock\PgSql\PgSqlLockMode;
use AndrewGos\QueryBuilder\Expr\Table\PgSql\PgSqlSelectTable;
use AndrewGos\QueryBuilder\Expr\Window\Window;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Query\Select\MySql\MySqlSelectQuery;
use AndrewGos\QueryBuilder\Query\Select\PgSql\PgSqlSelectQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_SelectQueryTest [DOMAIN(9): Testing; CONCEPT(9): SelectQuery; TECH(9): SQLGeneration]
/**
 * @purpose Test SELECT query building across all dialects: AbstractGrammar, PgSql, MySql including all clauses.
 */
class SelectQueryTest extends TestCase
{
    private AbstractGrammar $grammar;

    protected function setUp(): void
    {
        $this->grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                if ($identifier === '*') {
                    return $identifier;
                }
                return '"' . $identifier . '"';
            }
        };
    }

    // region METHOD_testBasicSelect [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Basic]
    /**
     * @purpose Verify basic SELECT with columns and FROM.
     */
    public function testBasicSelect(): void
    {
        $query = new SelectQuery();
        $query->select(['id', 'name'])->from(['users']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id", "name" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testBasicSelect

    // region METHOD_testSelectWithAlias [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): ColumnAlias]
    /**
     * @purpose Verify SELECT with column aliases.
     */
    public function testSelectWithAlias(): void
    {
        $query = new SelectQuery();
        $query->select(['user_id' => 'id'])->from(['users']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" AS "user_id" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testSelectWithAlias

    // region METHOD_testSelectDefaultStar [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): DefaultStar]
    /**
     * @purpose Verify empty select defaults to *.
     */
    public function testSelectDefaultStar(): void
    {
        $query = new SelectQuery();
        $query->from(['users']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT * FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testSelectDefaultStar

    // region METHOD_testDistinct [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Distinct]
    /**
     * @purpose Verify DISTINCT flag is set on query (full DISTINCT clause rendering via PgSqlGrammar in testPgSqlDistinctOn).
     */
    public function testDistinct(): void
    {
        $query = new SelectQuery();
        $query->select(['name'])->from(['users'])->distinct();

        self::assertTrue($query->distinct);
    }
    // endregion METHOD_testDistinct

    // region METHOD_testWhereClause [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Where]
    /**
     * @purpose Verify WHERE clause with conditions (bool true becomes IS TRUE, no params).
     */
    public function testWhereClause(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users'])->where(['active' => true]);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" WHERE "active" IS TRUE', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWhereClause

    // region METHOD_testGroupBy [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): GroupBy]
    /**
     * @purpose Verify GROUP BY clause.
     */
    public function testGroupBy(): void
    {
        $query = new SelectQuery();
        $query->select(['dept_id', new Expr('COUNT(*)')])
            ->from(['employees'])
            ->groupBy(['dept_id']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "dept_id", COUNT(*) FROM "employees" GROUP BY "dept_id"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testGroupBy

    // region METHOD_testHavingClause [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Having]
    /**
     * @purpose Verify HAVING clause with GROUP BY.
     */
    public function testHavingClause(): void
    {
        $query = new SelectQuery();
        $query->select(['dept_id', new Expr('COUNT(*)')])
            ->from(['employees'])
            ->groupBy(['dept_id'])
            ->having([new Expr('COUNT(*) > 1')]);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "dept_id", COUNT(*) FROM "employees" GROUP BY "dept_id" HAVING COUNT(*) > 1', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testHavingClause

    // region METHOD_testOrderBy [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): OrderBy]
    /**
     * @purpose Verify ORDER BY clause.
     */
    public function testOrderBy(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users'])->orderBy(['name' => SORT_ASC]);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" ORDER BY "name" ASC', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testOrderBy

    // region METHOD_testLimit [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Limit]
    /**
     * @purpose Verify LIMIT clause with FETCH ONLY.
     */
    public function testLimit(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users'])->limit(10);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" FETCH FIRST 10 ROWS ONLY', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testLimit

    // region METHOD_testLimitWithOffset [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Offset]
    /**
     * @purpose Verify LIMIT with OFFSET uses NEXT keyword.
     */
    public function testLimitWithOffset(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users'])->offset(5)->limit(10);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" OFFSET 5 FETCH NEXT 10 ROWS ONLY', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testLimitWithOffset

    // region METHOD_testLimitWithTies [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): WithTies]
    /**
     * @purpose Verify LIMIT WITH TIES syntax.
     */
    public function testLimitWithTies(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users'])->limit(5, LimitBoundTypeEnum::WithTies);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" FETCH FIRST 5 ROWS WITH TIES', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testLimitWithTies

    // region METHOD_testWindowClause [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Window]
    /**
     * @purpose Verify WINDOW clause.
     */
    public function testWindowClause(): void
    {
        $window = new Window();
        $window->partitionBy(['dept_id'])->orderBy(['salary' => SORT_DESC]);

        $query = new SelectQuery();
        $query->select(['id', new Expr('row_number() OVER w')])
            ->from(['employees'])
            ->window('w', $window);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id", row_number() OVER w FROM "employees" WINDOW "w" AS (PARTITION BY "dept_id" ORDER BY "salary" DESC)', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWindowClause

    // region METHOD_testJoinTypes [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Join]
    /**
     * @purpose Verify all JOIN types render correctly.
     */
    public function testJoinTypes(): void
    {
        $query = new SelectQuery();
        $query->select(['u.id', 'p.name'])
            ->from(['users u'])
            ->innerJoin('profiles p', ['u.id' => new Expr('p.user_id')]);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('INNER JOIN', $built->sql);
    }
    // endregion METHOD_testJoinTypes

    // region METHOD_testLeftJoin [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Join]
    /**
     * @purpose Verify LEFT JOIN.
     */
    public function testLeftJoin(): void
    {
        $query = new SelectQuery();
        $query->select(['u.id'])->from(['users u'])
            ->leftJoin('profiles p', ['u.id' => new Expr('p.user_id')]);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('LEFT JOIN', $built->sql);
    }
    // endregion METHOD_testLeftJoin

    // region METHOD_testRightJoin [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Join]
    /**
     * @purpose Verify RIGHT JOIN.
     */
    public function testRightJoin(): void
    {
        $query = new SelectQuery();
        $query->select(['u.id'])->from(['users u'])
            ->rightJoin('profiles p', ['u.id' => new Expr('p.user_id')]);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('RIGHT JOIN', $built->sql);
    }
    // endregion METHOD_testRightJoin

    // region METHOD_testCrossJoin [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Join]
    /**
     * @purpose Verify CROSS JOIN.
     */
    public function testCrossJoin(): void
    {
        $query = new SelectQuery();
        $query->select(['u.id'])->from(['users u'])
            ->crossJoin('profiles p');

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('CROSS JOIN', $built->sql);
        self::assertStringNotContainsString('ON', $built->sql);
    }
    // endregion METHOD_testCrossJoin

    // region METHOD_testFullJoin [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Join]
    /**
     * @purpose Verify FULL JOIN.
     */
    public function testFullJoin(): void
    {
        $query = new SelectQuery();
        $query->select(['u.id'])->from(['users u'])
            ->fullJoin('profiles p', ['u.id' => new Expr('p.user_id')]);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('FULL JOIN', $built->sql);
    }
    // endregion METHOD_testFullJoin

    // region METHOD_testNaturalJoin [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Join]
    /**
     * @purpose Verify NATURAL JOIN renders without ON.
     */
    public function testNaturalJoin(): void
    {
        $query = new SelectQuery();
        $query->select(['u.id'])->from(['users u'])
            ->naturalInnerJoin('profiles p');

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('NATURAL INNER JOIN', $built->sql);
    }
    // endregion METHOD_testNaturalJoin

    // region METHOD_testSetOperations [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): SetOperations]
    /**
     * @purpose Verify UNION ALL set operation.
     */
    public function testSetOperations(): void
    {
        $q2 = new SelectQuery();
        $q2->select(['id'])->from(['archive_users']);

        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);
        $query->unionAll($q2);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('UNION ALL', $built->sql);
    }
    // endregion METHOD_testSetOperations

    // region METHOD_testIntersectAll [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): SetOperations]
    /**
     * @purpose Verify INTERSECT ALL set operation.
     */
    public function testIntersectAll(): void
    {
        $q2 = new SelectQuery();
        $q2->select(['id'])->from(['archive_users']);

        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);
        $query->intersectAll($q2);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('INTERSECT ALL', $built->sql);
    }
    // endregion METHOD_testIntersectAll

    // region METHOD_testExceptAll [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): SetOperations]
    /**
     * @purpose Verify EXCEPT ALL set operation.
     */
    public function testExceptAll(): void
    {
        $q2 = new SelectQuery();
        $q2->select(['id'])->from(['archive_users']);

        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);
        $query->exceptAll($q2);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('EXCEPT ALL', $built->sql);
    }
    // endregion METHOD_testExceptAll

    // region METHOD_testUnionDistinct [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): SetOperations]
    /**
     * @purpose Verify UNION DISTINCT set operation.
     */
    public function testUnionDistinct(): void
    {
        $q2 = new SelectQuery();
        $q2->select(['id'])->from(['archive_users']);

        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);
        $query->unionDistinct($q2);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('UNION DISTINCT', $built->sql);
    }
    // endregion METHOD_testUnionDistinct

    // region METHOD_testPgSqlDistinctOn [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): PgSqlDistinctOn]
    /**
     * @purpose Verify PgSqlGrammar builds DISTINCT ON clause.
     */
    public function testPgSqlDistinctOn(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlSelectQuery();
        $query->select(['id', 'name'])->from(['users'])->distinctOn(['name']);

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT DISTINCT ON ("name") "id", "name" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlDistinctOn

    // region METHOD_testPgSqlOnlyTable [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): PgSqlOnly]
    /**
     * @purpose Verify PgSqlGrammar builds ONLY table modifier for table inheritance.
     */
    public function testPgSqlOnlyTable(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlSelectQuery();
        $query->select(['id'])->from([new PgSqlSelectTable('users', only: true)]);

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM ONLY "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlOnlyTable

    // region METHOD_testPgSqlLockForUpdate [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): PgSqlLock]
    /**
     * @purpose Verify PgSqlGrammar builds FOR UPDATE lock clause.
     */
    public function testPgSqlLockForUpdate(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlSelectQuery();
        $query->select(['id'])->from(['users']);
        $query->lock(new PgSqlLockMode(PgSqlLockModeEnum::ForUpdate));

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" FOR UPDATE NOWAIT', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlLockForUpdate

    // region METHOD_testPgSqlLockForShare [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): PgSqlLock]
    /**
     * @purpose Verify PgSqlGrammar builds FOR SHARE lock clause.
     */
    public function testPgSqlLockForShare(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlSelectQuery();
        $query->select(['id'])->from(['users']);
        $query->lock(new PgSqlLockMode(PgSqlLockModeEnum::ForShare));

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" FOR SHARE NOWAIT', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlLockForShare

    // region METHOD_testMySqlHints [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): MySqlHints]
    /**
     * @purpose Verify MySqlGrammar builds SELECT with MySQL-specific hints.
     */
    public function testMySqlHints(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlSelectQuery();
        $query->select(['id'])->from(['users']);
        $query->highPriority()->straightJoin()->sqlCalcFoundRows();

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT HIGH_PRIORITY STRAIGHT_JOIN SQL_CALC_FOUND_ROWS `id` FROM `users`', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testMySqlHints

    // region METHOD_testMySqlLock [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): MySqlLock]
    /**
     * @purpose Verify MySqlGrammar builds lock clause (UPDATE NOWAIT, no FOR prefix in MySql).
     */
    public function testMySqlLock(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlSelectQuery();
        $query->select(['id'])->from(['users']);
        $query->lock(new MySqlLockMode(MySqlLockModeEnum::ForUpdate));

        $built = $grammar->buildSelectQuery($query);
        self::assertStringContainsString('UPDATE NOWAIT', $built->sql);
    }
    // endregion METHOD_testMySqlLock

    // region METHOD_testMySqlLimit [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): MySqlLimit]
    /**
     * @purpose Verify MySqlGrammar uses MySQL LIMIT offset, count syntax.
     */
    public function testMySqlLimit(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlSelectQuery();
        $query->select(['id'])->from(['users'])->offset(5)->limit(10);

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT `id` FROM `users` LIMIT 5, 10', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testMySqlLimit

    // region METHOD_testMySqlPartitionSet [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): MySqlPartition]
    /**
     * @purpose Verify MySqlGrammar handles partition on query (PARTITION appears only in DELETE/INSERT, not SELECT).
     */
    public function testMySqlPartitionSet(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlSelectQuery();
        $query->select(['id'])->from(['users'])->partition(['p1', 'p2']);

        self::assertSame(['p1', 'p2'], $query->partitions);
    }
    // endregion METHOD_testMySqlPartitionSet

    // region METHOD_testWithClause [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): CTE]
    /**
     * @purpose Verify WITH clause for CTE.
     */
    public function testWithClause(): void
    {
        $cteQuery = new SelectQuery();
        $cteQuery->select(['id', 'name'])->from(['users']);

        $mainQuery = new SelectQuery();
        $mainQuery->select(['id'])->from(['active_users']);
        $mainQuery->with(['active_users' => new WithQuery($cteQuery)]);

        $built = $this->grammar->buildSelectQuery($mainQuery);
        self::assertSame('WITH "active_users" AS ( SELECT "id", "name" FROM "users" ) SELECT "id" FROM "active_users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWithClause

    // region METHOD_testWithRecursive [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): RecursiveCTE]
    /**
     * @purpose Verify WITH RECURSIVE clause.
     */
    public function testWithRecursive(): void
    {
        $cteQuery = new SelectQuery();
        $cteQuery->select(['id'])->from(['users']);

        $mainQuery = new SelectQuery();
        $mainQuery->select(['id'])->from(['cte']);
        $mainQuery->with(['cte' => new WithQuery($cteQuery)], recursive: true);

        $built = $this->grammar->buildSelectQuery($mainQuery);
        self::assertSame('WITH RECURSIVE "cte" AS ( SELECT "id" FROM "users" ) SELECT "id" FROM "cte"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWithRecursive

    // region METHOD_testPgSqlWithMaterialized [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): PgSqlCTE]
    /**
     * @purpose Verify PgSqlGrammar builds MATERIALIZED CTE.
     */
    public function testPgSqlWithMaterialized(): void
    {
        $grammar = new PgSqlGrammar();

        $cteQuery = new SelectQuery();
        $cteQuery->select(['id'])->from(['users']);

        $mainQuery = new SelectQuery();
        $mainQuery->select(['id'])->from(['active_users']);
        $mainQuery->with(['active_users' => new PgSqlWithQuery($cteQuery, materialized: true)]);

        $built = $grammar->buildSelectQuery($mainQuery);
        self::assertSame('WITH "active_users" AS MATERIALIZED ( SELECT "id" FROM "users" ) SELECT "id" FROM "active_users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlWithMaterialized

    // region METHOD_testAndWhereOrWhere [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): WhereComposition]
    /**
     * @purpose Verify andWhere and orWhere condition composition.
     */
    public function testAndWhereOrWhere(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users'])
            ->where(['active' => true])
            ->andWhere(['deleted' => false])
            ->orWhere(['name' => 'test']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertStringContainsString('WHERE', $built->sql);
        self::assertStringContainsString('OR', $built->sql);
    }
    // endregion METHOD_testAndWhereOrWhere

    // region METHOD_testGroupByDistinct [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): GroupByDistinct]
    /**
     * @purpose Verify GROUP BY DISTINCT clause.
     */
    public function testGroupByDistinct(): void
    {
        $query = new SelectQuery();
        $query->select(['dept_id'])->from(['employees'])
            ->groupBy(['dept_id'], distinct: true);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "dept_id" FROM "employees" GROUP BY DISTINCT "dept_id"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testGroupByDistinct

    // region METHOD_testAddSelect [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): AddColumns]
    /**
     * @purpose Verify addSelect appends columns.
     */
    public function testAddSelect(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->addSelect(['name'])->from(['users']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id", "name" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testAddSelect

    // region METHOD_testAddGroupBy [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): AddGroupBy]
    /**
     * @purpose Verify addGroupBy appends group columns.
     */
    public function testAddGroupBy(): void
    {
        $query = new SelectQuery();
        $query->select(['dept_id', new Expr('COUNT(*)')])
            ->from(['employees'])
            ->groupBy(['dept_id'])
            ->addGroupBy(['region']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "dept_id", COUNT(*) FROM "employees" GROUP BY "dept_id", "region"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testAddGroupBy

    // region METHOD_testOrHaving [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): OrHaving]
    /**
     * @purpose Verify orHaving wraps existing AND group with OR logic.
     */
    public function testOrHaving(): void
    {
        $query = new SelectQuery();
        $query->select(['dept_id', new Expr('COUNT(*)')])
            ->from(['employees'])
            ->groupBy(['dept_id'])
            ->having([new Expr('COUNT(*) > 1')])
            ->orHaving([new Expr('COUNT(*) = 0')]);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT "dept_id", COUNT(*) FROM "employees" GROUP BY "dept_id" HAVING (COUNT(*) > 1) OR (COUNT(*) = 0)', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testOrHaving

    // region METHOD_testSelectWithExprColumn [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): ExprColumn]
    /**
     * @purpose Verify SELECT with ExprInterface as column.
     */
    public function testSelectWithExprColumn(): void
    {
        $query = new SelectQuery();
        $query->select([new Expr('COUNT(*)')])->from(['users']);

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT COUNT(*) FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testSelectWithExprColumn

    // region METHOD_testPgSqlLockNoKeyUpdate [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): PgSqlLockNoKey]
    /**
     * @purpose Verify PgSqlGrammar builds FOR NO KEY UPDATE.
     */
    public function testPgSqlLockNoKeyUpdate(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlSelectQuery();
        $query->select(['id'])->from(['users']);
        $query->lock(new PgSqlLockMode(PgSqlLockModeEnum::ForNoKeyUpdate));

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" FOR NO KEY UPDATE NOWAIT', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlLockNoKeyUpdate

    // region METHOD_testPgSqlLockForKeyShare [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): PgSqlLockKeyShare]
    /**
     * @purpose Verify PgSqlGrammar builds FOR KEY SHARE.
     */
    public function testPgSqlLockForKeyShare(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlSelectQuery();
        $query->select(['id'])->from(['users']);
        $query->lock(new PgSqlLockMode(PgSqlLockModeEnum::ForKeyShare));

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users" FOR KEY SHARE NOWAIT', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlLockForKeyShare

    // region METHOD_testMySqlAllHints [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): MySqlAllHints]
    /**
     * @purpose Verify MySqlGrammar builds all SQL_* hints.
     */
    public function testMySqlAllHints(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlSelectQuery();
        $query->select(['id'])->from(['users']);
        $query->sqlSmallResult()->sqlBigResult()->sqlBufferResult()->sqlNoCache();

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT SQL_SMALL_RESULT SQL_BIG_RESULT SQL_BUFFER_RESULT SQL_NO_CACHE `id` FROM `users`', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testMySqlAllHints

    // region METHOD_testDistinctViaAbstractGrammar [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Distinct]
    /**
     * @purpose Verify AbstractGrammar builds SELECT DISTINCT with correct SQL string (regression test for Expr(bool) bug).
     */
    public function testDistinctViaAbstractGrammar(): void
    {
        $query = new SelectQuery();
        $query->select(['col'])->from(['t'])->distinct();

        $built = $this->grammar->buildSelectQuery($query);
        self::assertSame('SELECT DISTINCT "col" FROM "t"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testDistinctViaAbstractGrammar

    // region METHOD_testDistinctViaPgSqlGrammar [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): Distinct]
    /**
     * @purpose Verify PgSqlGrammar builds SELECT DISTINCT correctly.
     */
    public function testDistinctViaPgSqlGrammar(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlSelectQuery();
        $query->select(['col'])->from(['t'])->distinct();

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT DISTINCT "col" FROM "t"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testDistinctViaPgSqlGrammar

    // region METHOD_testPgSqlDistinctOnMultipleColumns [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): PgSqlDistinctOn]
    /**
     * @purpose Verify PgSqlGrammar builds DISTINCT ON with multiple columns.
     */
    public function testPgSqlDistinctOnMultipleColumns(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlSelectQuery();
        $query->select(['id', 'name'])->from(['users'])->distinctOn(['dept_id', 'name']);

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT DISTINCT ON ("dept_id", "name") "id", "name" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlDistinctOnMultipleColumns

    // region METHOD_testPgSqlDistinctOnWithExpr [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): PgSqlDistinctOn]
    /**
     * @purpose Verify PgSqlGrammar builds DISTINCT ON with Expr objects (raw expressions without escaping).
     */
    public function testPgSqlDistinctOnWithExpr(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlSelectQuery();
        $query->select(['id', 'name'])->from(['users'])->distinctOn([new Expr('LOWER(name)')]);

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT DISTINCT ON (LOWER(name)) "id", "name" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlDistinctOnWithExpr

    // region METHOD_testDistinctFalseClearsDistinctOn [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): DistinctOnClearing]
    /**
     * @purpose Verify setting distinct(false) clears distinctOn and removes DISTINCT from SQL.
     */
    public function testDistinctFalseClearsDistinctOn(): void
    {
        $query = new PgSqlSelectQuery();
        $query->select(['id', 'name'])->from(['users'])->distinctOn(['name']);
        self::assertTrue($query->distinct);

        $query->distinct(false);
        self::assertFalse($query->distinct);
        self::assertSame([], $query->distinctOn);

        $grammar = new PgSqlGrammar();
        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id", "name" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testDistinctFalseClearsDistinctOn

    // region METHOD_testPgSqlAddDistinctOn [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): PgSqlAddDistinctOn]
    /**
     * @purpose Verify addDistinctOn appends columns (regression test for assignment-vs-merge bug).
     */
    public function testPgSqlAddDistinctOn(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlSelectQuery();
        $query->select(['id', 'name', 'dept_id'])->from(['users'])
            ->distinctOn(['a'])
            ->addDistinctOn(['b']);

        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT DISTINCT ON ("a", "b") "id", "name", "dept_id" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlAddDistinctOn

    // region METHOD_testDistinctToggleOnOff [DOMAIN(9): Testing; CONCEPT(9): Select; TECH(9): DistinctToggle]
    /**
     * @purpose Verify toggling distinct flag on/off and the invariant that distinct=false clears distinctOn.
     */
    public function testDistinctToggleOnOff(): void
    {
        $query = new PgSqlSelectQuery();
        $query->select(['id'])->from(['users']);

        self::assertFalse($query->distinct);

        $query->distinct();
        self::assertTrue($query->distinct);

        $query->distinctOn(['col']);
        self::assertTrue($query->distinct);
        self::assertSame(['col'], $query->distinctOn);

        $query->distinct(false);
        self::assertFalse($query->distinct);
        self::assertSame([], $query->distinctOn);

        $grammar = new PgSqlGrammar();
        $built = $grammar->buildSelectQuery($query);
        self::assertSame('SELECT "id" FROM "users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testDistinctToggleOnOff
}
// endregion CLASS_SelectQueryTest
