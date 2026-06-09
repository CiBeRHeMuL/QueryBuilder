<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\MySql\MySqlLockModeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\MySql\MySqlLockWaitModeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\PgSql\PgSqlLockModeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\PgSql\PgSqlLockWaitModeEnum;
use AndrewGos\QueryBuilder\Enum\Window\FrameBoundEnum;
use AndrewGos\QueryBuilder\Enum\Window\FrameExclusionEnum;
use AndrewGos\QueryBuilder\Enum\Window\FrameTypeEnum;
use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\Lock\MySql\MySqlLockMode;
use AndrewGos\QueryBuilder\Expr\Lock\PgSql\PgSqlLockMode;
use AndrewGos\QueryBuilder\Expr\Window\Over;
use AndrewGos\QueryBuilder\Expr\Window\Window;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Query\Delete\DeleteQuery;
use AndrewGos\QueryBuilder\Query\Delete\MySql\MySqlDeleteQuery;
use AndrewGos\QueryBuilder\Query\Delete\PgSql\PgSqlDeleteQuery;
use AndrewGos\QueryBuilder\Query\Merge\MergeQuery;
use AndrewGos\QueryBuilder\Query\Select\MySql\MySqlSelectQuery;
use AndrewGos\QueryBuilder\Query\Select\PgSql\PgSqlSelectQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_QueryEdgeCaseTest [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): SQLGeneration]
/**
 * @purpose E2E tests covering edge cases: trait add* methods, join variants, set operations, window frame edge cases, lock modes without tables, MySQL hints and partition, PgSql USING.
 */
class QueryEdgeCaseTest extends TestCase
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

    // region METHOD_testAddFrom [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): FromTrait]
    /**
     * @purpose Verify FromTrait::addFrom appends tables to FROM clause.
     */
    public function testAddFrom(): void
    {
        $q = new SelectQuery();
        $q->select(['*'])->from(['a'])->addFrom(['b']);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "a", "b"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testAddFrom

    // region METHOD_testAddOrderBy [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): OrderByTrait]
    /**
     * @purpose Verify OrderByTrait::addOrderBy appends columns to ORDER BY clause.
     */
    public function testAddOrderBy(): void
    {
        $q = new SelectQuery();
        $q->select(['id'])->from(['t'])->orderBy(['a' => 'ASC'])->addOrderBy(['b' => 'DESC']);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT "id" FROM "t" ORDER BY "a" ASC, "b" DESC', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testAddOrderBy

    // region METHOD_testAddWith [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): WithTrait]
    /**
     * @purpose Verify WithTrait::addWith merges CTE definitions.
     */
    public function testAddWith(): void
    {
        $cte1 = new WithQuery(new SelectQuery()->select([new Expr('1')]));
        $cte2 = new WithQuery(new SelectQuery()->select([new Expr('2')]));
        $q = new SelectQuery();
        $q->select(['*'])->from(['t'])->with(['cte1' => $cte1])->addWith(['cte2' => $cte2]);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('WITH "cte1" AS ( SELECT 1 ), "cte2" AS ( SELECT 2 ) SELECT * FROM "t"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testAddWith

    // region METHOD_testAddPartition [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): PartitionTrait]
    /**
     * @purpose Verify MySql PartitionTrait::addPartition appends partition names.
     */
    public function testAddPartition(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlDeleteQuery();
        $q->from(['t'])->where(['id' => 1])->partition(['p1'])->addPartition(['p2']);
        $built = $grammar->buildDeleteQuery($q);
        // Justification: WHERE value 1 is converted to a named param placeholder
        self::assertMatchesRegularExpression(
            '/^DELETE FROM `t` WHERE `id` = :v\d+_\d+ PARTITION \(`p1`, `p2`\)$/',
            $built->sql,
        );
    }
    // endregion METHOD_testAddPartition

    // region METHOD_testSingleFromTrait [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): SingleFromTrait]
    /**
     * @purpose Verify SingleFromTrait takes only first table, addFrom delegates to from (replaces).
     */
    public function testSingleFromTrait(): void
    {
        $q = new DeleteQuery();
        $q->from(['a', 'b']);
        self::assertCount(1, $q->from);

        $q->addFrom(['c']);
        self::assertCount(1, $q->from);

        $built = $this->grammar->buildDeleteQuery($q);
        self::assertSame('DELETE FROM "c"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testSingleFromTrait

    // region METHOD_testPgSqlDeleteAddUsing [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): PgSqlDelete]
    /**
     * @purpose Verify PgSqlDeleteQuery::addUsing appends USING tables.
     */
    public function testPgSqlDeleteAddUsing(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlDeleteQuery();
        $q->from(['t'])->using(['u1'])->addUsing(['u2']);
        $built = $grammar->buildDeleteQuery($q);
        self::assertSame('DELETE FROM "t" USING "u1", "u2"', $built->sql);
    }
    // endregion METHOD_testPgSqlDeleteAddUsing

    // region METHOD_testCrossJoin [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Join]
    /**
     * @purpose Verify CROSS JOIN rendering.
     */
    public function testCrossJoin(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['a'])->crossJoin('b');
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "a" CROSS JOIN "b" ', $built->sql);
    }
    // endregion METHOD_testCrossJoin

    // region METHOD_testRightJoin [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Join]
    /**
     * @purpose Verify RIGHT OUTER JOIN rendering.
     */
    public function testRightJoin(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['a'])->rightJoin('b', ['a.id' => 'b.id']);
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "a" RIGHT JOIN "b" ON "a"."id" = "b"."id"', $built->sql);
    }
    // endregion METHOD_testRightJoin

    // region METHOD_testFullJoin [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Join]
    /**
     * @purpose Verify FULL JOIN rendering (JoinTypeEnum returns 'FULL JOIN' without OUTER).
     */
    public function testFullJoin(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['a'])->fullJoin('b', ['a.id' => 'b.id']);
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "a" FULL JOIN "b" ON "a"."id" = "b"."id"', $built->sql);
    }
    // endregion METHOD_testFullJoin

    // region METHOD_testNaturalInnerJoin [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Join]
    /**
     * @purpose Verify NATURAL INNER JOIN rendering. Note trailing space from sprintf format with empty ON.
     */
    public function testNaturalInnerJoin(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['a'])->naturalInnerJoin('b');
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "a" NATURAL INNER JOIN "b" ', $built->sql);
    }
    // endregion METHOD_testNaturalInnerJoin

    // region METHOD_testNaturalLeftJoin [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Join]
    /**
     * @purpose Verify NATURAL LEFT JOIN rendering (JoinTypeEnum returns 'LEFT JOIN', not 'LEFT OUTER JOIN').
     */
    public function testNaturalLeftJoin(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['a'])->naturalLeftJoin('b');
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "a" NATURAL LEFT JOIN "b" ', $built->sql);
    }
    // endregion METHOD_testNaturalLeftJoin

    // region METHOD_testNaturalRightJoin [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Join]
    /**
     * @purpose Verify NATURAL RIGHT JOIN rendering.
     */
    public function testNaturalRightJoin(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['a'])->naturalRightJoin('b');
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "a" NATURAL RIGHT JOIN "b" ', $built->sql);
    }
    // endregion METHOD_testNaturalRightJoin

    // region METHOD_testNaturalFullJoin [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Join]
    /**
     * @purpose Verify NATURAL FULL JOIN rendering.
     */
    public function testNaturalFullJoin(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['a'])->naturalFullJoin('b');
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "a" NATURAL FULL JOIN "b" ', $built->sql);
    }
    // endregion METHOD_testNaturalFullJoin

    // region METHOD_testNaturalJoinThrowsOnCrossJoin [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Join]
    /**
     * @purpose Verify naturalJoin throws on CrossJoin type.
     */
    public function testNaturalJoinThrowsOnCrossJoin(): void
    {
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['a']);
        $this->expectException(QueryBuilderException::class);
        // Justification: Message contains the join type name — exact text depends on JoinTypeEnum::CrossJoin->getSql()
        $this->expectExceptionMessageMatches('/Cannot use join type ".*" with natural join/');
        $q->naturalJoin(JoinTypeEnum::CrossJoin, 'b');
    }
    // endregion METHOD_testNaturalJoinThrowsOnCrossJoin

    // region METHOD_testIntersectAll [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): SetOperations]
    /**
     * @purpose Verify INTERSECT ALL set operation.
     */
    public function testIntersectAll(): void
    {
        $sq = new SelectQuery()->select(['id'])->from(['b']);
        $q = new SelectQuery();
        $q->select(['id'])->from(['a'])->intersectAll($sq);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT "id" FROM "a" INTERSECT ALL (SELECT "id" FROM "b")', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testIntersectAll

    // region METHOD_testExceptAll [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): SetOperations]
    /**
     * @purpose Verify EXCEPT ALL set operation.
     */
    public function testExceptAll(): void
    {
        $sq = new SelectQuery()->select(['id'])->from(['b']);
        $q = new SelectQuery();
        $q->select(['id'])->from(['a'])->exceptAll($sq);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT "id" FROM "a" EXCEPT ALL (SELECT "id" FROM "b")', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testExceptAll

    // region METHOD_testUnionDistinct [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): SetOperations]
    /**
     * @purpose Verify UNION DISTINCT set operation.
     */
    public function testUnionDistinct(): void
    {
        $sq = new SelectQuery()->select(['id'])->from(['b']);
        $q = new SelectQuery();
        $q->select(['id'])->from(['a'])->unionDistinct($sq);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT "id" FROM "a" UNION DISTINCT (SELECT "id" FROM "b")', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testUnionDistinct

    // region METHOD_testIntersectDistinct [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): SetOperations]
    /**
     * @purpose Verify INTERSECT DISTINCT set operation.
     */
    public function testIntersectDistinct(): void
    {
        $sq = new SelectQuery()->select(['id'])->from(['b']);
        $q = new SelectQuery();
        $q->select(['id'])->from(['a'])->intersectDistinct($sq);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT "id" FROM "a" INTERSECT DISTINCT (SELECT "id" FROM "b")', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testIntersectDistinct

    // region METHOD_testExceptDistinct [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): SetOperations]
    /**
     * @purpose Verify EXCEPT DISTINCT set operation.
     */
    public function testExceptDistinct(): void
    {
        $sq = new SelectQuery()->select(['id'])->from(['b']);
        $q = new SelectQuery();
        $q->select(['id'])->from(['a'])->exceptDistinct($sq);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT "id" FROM "a" EXCEPT DISTINCT (SELECT "id" FROM "b")', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testExceptDistinct

    // region METHOD_testWindowGroups [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Window]
    /**
     * @purpose Verify Window::groups with realistic ROW_NUMBER() OVER (GROUPS BETWEEN ...) pattern.
     */
    public function testWindowGroups(): void
    {
        $w = new Window()->groups(FrameBoundEnum::Preceding, FrameBoundEnum::Following, 3, 5);
        $over = new Over(new Expr('ROW_NUMBER()'), $w->orderBy(['salary' => 'DESC']));
        $q = new SelectQuery();
        $q->select(['rn' => $over])->from(['emps']);
        $built = $this->grammar->buildSelectQuery($q);
        // Justification: integer offsets 3 and 5 are converted to named param placeholders by ValueBuilder
        self::assertMatchesRegularExpression(
            '/^SELECT ROW_NUMBER\(\) OVER \(ORDER BY "salary" DESC GROUPS BETWEEN :v\d+_\d+ PRECEDING AND :v\d+_\d+ FOLLOWING\) AS "rn" FROM "emps"$/',
            $built->sql,
        );
        self::assertCount(2, $built->params);
        self::assertSame([3, 5], array_values($built->params));
    }
    // endregion METHOD_testWindowGroups

    // region METHOD_testWindowExtend [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Window]
    /**
     * @purpose Verify Over with named window reference via ROW_NUMBER() OVER "existing_w".
     */
    public function testWindowExtend(): void
    {
        $over = new Over(new Expr('ROW_NUMBER()'), 'existing_w');
        $q = new SelectQuery();
        $q->select(['rn' => $over])->from(['t']);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT ROW_NUMBER() OVER "existing_w" AS "rn" FROM "t"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWindowExtend

    // region METHOD_testWindowAddPartitionBy [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Window]
    /**
     * @purpose Verify Window::addPartitionBy with realistic ROW_NUMBER() OVER (PARTITION BY ...) pattern.
     */
    public function testWindowAddPartitionBy(): void
    {
        $w = new Window()->partitionBy(['dept_id'])->addPartitionBy(['year']);
        $over = new Over(new Expr('ROW_NUMBER()'), $w);
        $q = new SelectQuery();
        $q->select(['rn' => $over])->from(['t']);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT ROW_NUMBER() OVER (PARTITION BY "dept_id", "year") AS "rn" FROM "t"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWindowAddPartitionBy

    // region METHOD_testWindowAddOrderBy [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Window]
    /**
     * @purpose Verify Window::addOrderBy with realistic ROW_NUMBER() OVER (ORDER BY ...) pattern.
     */
    public function testWindowAddOrderBy(): void
    {
        $w = new Window()->orderBy(['salary' => 'DESC'])->addOrderBy(['name' => 'ASC']);
        $over = new Over(new Expr('ROW_NUMBER()'), $w);
        $q = new SelectQuery();
        $q->select(['rn' => $over])->from(['t']);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT ROW_NUMBER() OVER (ORDER BY "salary" DESC, "name" ASC) AS "rn" FROM "t"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWindowAddOrderBy

    // region METHOD_testWindowFrameExclusionCurrentRow [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Window]
    /**
     * @purpose Verify Window frame with EXCLUDE CURRENT ROW via realistic ROW_NUMBER() OVER (...) pattern.
     */
    public function testWindowFrameExclusionCurrentRow(): void
    {
        $w = new Window()->frame(start: FrameBoundEnum::Preceding, end: FrameBoundEnum::CurrentRow, exclusion: FrameExclusionEnum::CurrentRow);
        $over = new Over(new Expr('ROW_NUMBER()'), $w->orderBy(['id' => 'ASC']));
        $q = new SelectQuery();
        $q->select(['rn' => $over])->from(['t']);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT ROW_NUMBER() OVER (ORDER BY "id" ASC RANGE BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW EXCLUDE CURRENT ROW) AS "rn" FROM "t"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWindowFrameExclusionCurrentRow

    // region METHOD_testWindowFrameExclusionGroup [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Window]
    /**
     * @purpose Verify Window frame with EXCLUDE GROUP via realistic ROW_NUMBER() OVER (...) pattern.
     */
    public function testWindowFrameExclusionGroup(): void
    {
        $w = new Window()->frame(exclusion: FrameExclusionEnum::Group);
        $over = new Over(new Expr('ROW_NUMBER()'), $w->orderBy(['id' => 'ASC']));
        $q = new SelectQuery();
        $q->select(['rn' => $over])->from(['t']);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT ROW_NUMBER() OVER (ORDER BY "id" ASC RANGE BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW EXCLUDE GROUP) AS "rn" FROM "t"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWindowFrameExclusionGroup

    // region METHOD_testWindowFrameExclusionTies [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Window]
    /**
     * @purpose Verify Window frame with EXCLUDE TIES via realistic ROW_NUMBER() OVER (...) pattern.
     */
    public function testWindowFrameExclusionTies(): void
    {
        $w = new Window()->frame(exclusion: FrameExclusionEnum::Ties);
        $over = new Over(new Expr('ROW_NUMBER()'), $w->orderBy(['id' => 'ASC']));
        $q = new SelectQuery();
        $q->select(['rn' => $over])->from(['t']);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT ROW_NUMBER() OVER (ORDER BY "id" ASC RANGE BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW EXCLUDE TIES) AS "rn" FROM "t"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWindowFrameExclusionTies

    // region METHOD_testWindowFrameExclusionNoOthers [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Window]
    /**
     * @purpose Verify Window frame with EXCLUDE NO OTHERS via realistic ROW_NUMBER() OVER (...) pattern.
     */
    public function testWindowFrameExclusionNoOthers(): void
    {
        $w = new Window()->frame(exclusion: FrameExclusionEnum::NoOthers);
        $over = new Over(new Expr('ROW_NUMBER()'), $w->orderBy(['id' => 'ASC']));
        $q = new SelectQuery();
        $q->select(['rn' => $over])->from(['t']);
        $built = $this->grammar->buildSelectQuery($q);
        self::assertSame('SELECT ROW_NUMBER() OVER (ORDER BY "id" ASC RANGE BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW EXCLUDE NO OTHERS) AS "rn" FROM "t"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testWindowFrameExclusionNoOthers

    // region METHOD_testWindowFrameWithExprOffset [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Window]
    /**
     * @purpose Verify Window frame with ExprInterface offsets via realistic ROW_NUMBER() OVER (ROWS BETWEEN ...) pattern.
     */
    public function testWindowFrameWithExprOffset(): void
    {
        $w = new Window()->frame(
            type: FrameTypeEnum::Rows,
            start: FrameBoundEnum::Preceding,
            end: FrameBoundEnum::Following,
            startOffset: new Expr(':v1', ['v1' => 3]),
            endOffset: new Expr(':v2', ['v2' => 5]),
        );
        $over = new Over(new Expr('ROW_NUMBER()'), $w->orderBy(['id' => 'ASC']));
        $q = new SelectQuery();
        $q->select(['rn' => $over])->from(['t']);
        $built = $this->grammar->buildSelectQuery($q);
        // Justification: param names from Expr are literal (':v1', ':v2'), not generated via generateParamId
        self::assertMatchesRegularExpression(
            '/^SELECT ROW_NUMBER\(\) OVER \(ORDER BY "id" ASC ROWS BETWEEN :v\d+ PRECEDING AND :v\d+ FOLLOWING\) AS "rn" FROM "t"$/',
            $built->sql,
        );
        self::assertCount(2, $built->params);
        self::assertSame([3, 5], array_values($built->params));
    }
    // endregion METHOD_testWindowFrameWithExprOffset

    // region METHOD_testMySqlLockModeWithoutTables [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Lock]
    /**
     * @purpose Verify MySqlLockMode with empty tables list renders mode + wait, no OF.
     */
    public function testMySqlLockModeWithoutTables(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlSelectQuery();
        $q->select(['*'])->from(['t'])->lock(new MySqlLockMode(mode: MySqlLockModeEnum::ForUpdate, tables: [], waitMode: MySqlLockWaitModeEnum::Nowait));
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM `t` FOR UPDATE NOWAIT', $built->sql);
    }
    // endregion METHOD_testMySqlLockModeWithoutTables

    // region METHOD_testMySqlLockModeWithTables [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Lock]
    /**
     * @purpose Verify MySqlLockMode with tables renders OF clause.
     */
    public function testMySqlLockModeWithTables(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlSelectQuery();
        $q->select(['*'])->from(['t'])->lock(new MySqlLockMode(mode: MySqlLockModeEnum::ForUpdate, tables: ['t1', 't2'], waitMode: MySqlLockWaitModeEnum::SkipLocked));
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM `t` FOR UPDATE OF `t1` `t2` SKIP LOCKED', $built->sql);
    }
    // endregion METHOD_testMySqlLockModeWithTables

    // region METHOD_testPgSqlLockModeWithoutTables [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Lock]
    /**
     * @purpose Verify PgSqlLockMode with empty tables list.
     */
    public function testPgSqlLockModeWithoutTables(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['t'])->lock(new PgSqlLockMode(mode: PgSqlLockModeEnum::ForUpdate, tables: [], waitMode: PgSqlLockWaitModeEnum::Nowait));
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "t" FOR UPDATE NOWAIT', $built->sql);
    }
    // endregion METHOD_testPgSqlLockModeWithoutTables

    // region METHOD_testPgSqlLockModeWithTables [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Lock]
    /**
     * @purpose Verify PgSqlLockMode with tables renders OF clause.
     */
    public function testPgSqlLockModeWithTables(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['t'])->lock(new PgSqlLockMode(mode: PgSqlLockModeEnum::ForKeyShare, tables: ['t1'], waitMode: PgSqlLockWaitModeEnum::SkipLocked));
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "t" FOR KEY SHARE OF "t1" SKIP LOCKED', $built->sql);
    }
    // endregion METHOD_testPgSqlLockModeWithTables

    // region METHOD_testPgSqlDistinctOn [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): PgSqlSelect]
    /**
     * @purpose Verify PgSqlSelectQuery::distinctOn renders DISTINCT ON clause.
     */
    public function testPgSqlDistinctOn(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['id', 'name'])->from(['emps'])->distinctOn(['dept_id']);
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT DISTINCT ON ("dept_id") "id", "name" FROM "emps"', $built->sql);
    }
    // endregion METHOD_testPgSqlDistinctOn

    // region METHOD_testPgSqlAddLock [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): PgSqlSelect]
    /**
     * @purpose Verify PgSqlSelectQuery::addLock appends lock modes.
     */
    public function testPgSqlAddLock(): void
    {
        $grammar = new PgSqlGrammar();
        $q = new PgSqlSelectQuery();
        $q->select(['*'])->from(['t']);
        $q->addLock(new PgSqlLockMode(mode: PgSqlLockModeEnum::ForUpdate, waitMode: PgSqlLockWaitModeEnum::Nowait));
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM "t" FOR UPDATE NOWAIT', $built->sql);
    }
    // endregion METHOD_testPgSqlAddLock

    // region METHOD_testMySqlAddLock [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): MySqlSelect]
    /**
     * @purpose Verify MySqlSelectQuery::addLock appends lock modes.
     */
    public function testMySqlAddLock(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlSelectQuery();
        $q->select(['*'])->from(['t']);
        $q->addLock(new MySqlLockMode(mode: MySqlLockModeEnum::ForShare, waitMode: MySqlLockWaitModeEnum::Nowait));
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT * FROM `t` FOR SHARE NOWAIT', $built->sql);
    }
    // endregion METHOD_testMySqlAddLock

    // region METHOD_testMySqlHighPriority [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): MySqlHint]
    /**
     * @purpose Verify MySqlSelectQuery HIGH_PRIORITY hint.
     */
    public function testMySqlHighPriority(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlSelectQuery();
        $q->select(['*'])->from(['t'])->highPriority();
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT HIGH_PRIORITY * FROM `t`', $built->sql);
    }
    // endregion METHOD_testMySqlHighPriority

    // region METHOD_testMySqlStraightJoin [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): MySqlHint]
    /**
     * @purpose Verify MySqlSelectQuery STRAIGHT_JOIN hint.
     */
    public function testMySqlStraightJoin(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlSelectQuery();
        $q->select(['*'])->from(['t'])->straightJoin();
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT STRAIGHT_JOIN * FROM `t`', $built->sql);
    }
    // endregion METHOD_testMySqlStraightJoin

    // region METHOD_testMySqlSqlSmallResult [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): MySqlHint]
    /**
     * @purpose Verify MySqlSelectQuery SQL_SMALL_RESULT hint.
     */
    public function testMySqlSqlSmallResult(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlSelectQuery();
        $q->select(['*'])->from(['t'])->sqlSmallResult();
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT SQL_SMALL_RESULT * FROM `t`', $built->sql);
    }
    // endregion METHOD_testMySqlSqlSmallResult

    // region METHOD_testMySqlSqlBigResult [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): MySqlHint]
    /**
     * @purpose Verify MySqlSelectQuery SQL_BIG_RESULT hint.
     */
    public function testMySqlSqlBigResult(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlSelectQuery();
        $q->select(['*'])->from(['t'])->sqlBigResult();
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT SQL_BIG_RESULT * FROM `t`', $built->sql);
    }
    // endregion METHOD_testMySqlSqlBigResult

    // region METHOD_testMySqlSqlBufferResult [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): MySqlHint]
    /**
     * @purpose Verify MySqlSelectQuery SQL_BUFFER_RESULT hint.
     */
    public function testMySqlSqlBufferResult(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlSelectQuery();
        $q->select(['*'])->from(['t'])->sqlBufferResult();
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT SQL_BUFFER_RESULT * FROM `t`', $built->sql);
    }
    // endregion METHOD_testMySqlSqlBufferResult

    // region METHOD_testMySqlSqlNoCache [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): MySqlHint]
    /**
     * @purpose Verify MySqlSelectQuery SQL_NO_CACHE hint.
     */
    public function testMySqlSqlNoCache(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlSelectQuery();
        $q->select(['*'])->from(['t'])->sqlNoCache();
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT SQL_NO_CACHE * FROM `t`', $built->sql);
    }
    // endregion METHOD_testMySqlSqlNoCache

    // region METHOD_testMySqlSqlCalcFoundRows [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): MySqlHint]
    /**
     * @purpose Verify MySqlSelectQuery SQL_CALC_FOUND_ROWS hint.
     */
    public function testMySqlSqlCalcFoundRows(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlSelectQuery();
        $q->select(['*'])->from(['t'])->sqlCalcFoundRows();
        $built = $grammar->buildSelectQuery($q);
        self::assertSame('SELECT SQL_CALC_FOUND_ROWS * FROM `t`', $built->sql);
    }
    // endregion METHOD_testMySqlSqlCalcFoundRows

    // region METHOD_testMySqlDeletePartition [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Partition]
    /**
     * @purpose Verify MySql DELETE with PARTITION clause.
     */
    public function testMySqlDeletePartition(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MySqlDeleteQuery();
        $q->from(['t'])->where(['id' => 1])->partition(['p1']);
        $built = $grammar->buildDeleteQuery($q);
        // Justification: WHERE value 1 is converted to a named param placeholder
        self::assertMatchesRegularExpression(
            '/^DELETE FROM `t` WHERE `id` = :v\d+_\d+ PARTITION \(`p1`\)$/',
            $built->sql,
        );
    }
    // endregion METHOD_testMySqlDeletePartition

    // region METHOD_testMySqlMergeThrows [DOMAIN(9): Testing; CONCEPT(9): EdgeCases; TECH(9): Merge]
    /**
     * @purpose Verify MySqlGrammar::buildMergeQuery throws for MySQL (unsupported).
     */
    public function testMySqlMergeThrows(): void
    {
        $grammar = new MySqlGrammar();
        $q = new MergeQuery();
        $q->into('target', 't')->using('source', 's')->on(['t.id' => 's.id']);
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('MERGE is not supported in MySQL');
        $grammar->buildMergeQuery($q);
    }
    // endregion METHOD_testMySqlMergeThrows
}
// endregion CLASS_QueryEdgeCaseTest
