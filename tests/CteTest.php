<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Enum\Cte\SearchTypeEnum;
use AndrewGos\QueryBuilder\Expr\Cte\Cycle;
use AndrewGos\QueryBuilder\Expr\Cte\PgSql\PgSqlWithQuery;
use AndrewGos\QueryBuilder\Expr\Cte\Search;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\Literal;
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_CteTest [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): SQLGeneration]
/**
 * @purpose Test CTE building: WithQuery, Search, Cycle, PgSqlWithQuery materialization.
 */
class CteTest extends TestCase
{
    // region METHOD_testWithQueryConstruction [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): Constructor]
    /**
     * @purpose Verify WithQuery constructor stores query and allows search/cycle.
     */
    public function testWithQueryConstruction(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);

        $withQuery = new WithQuery($query);
        self::assertSame($query, $withQuery->query);
        self::assertNull($withQuery->search);
        self::assertNull($withQuery->cycle);
    }
    // endregion METHOD_testWithQueryConstruction

    // region METHOD_testSearchClause [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): SearchValueObject]
    /**
     * @purpose Verify Search value object stores type, columns, and seq column name.
     */
    public function testSearchClause(): void
    {
        $search = new Search(SearchTypeEnum::Breadth, ['id', 'parent_id'], 'seq');
        self::assertSame(SearchTypeEnum::Breadth, $search->type);
        self::assertSame(['id', 'parent_id'], $search->columns);
        self::assertSame('seq', $search->searchSeqColumnName);
    }
    // endregion METHOD_testSearchClause

    // region METHOD_testSearchDepth [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): DepthFirst]
    /**
     * @purpose Verify Search with DEPTH type.
     */
    public function testSearchDepth(): void
    {
        $search = new Search(SearchTypeEnum::Depth, ['id'], 'seq');
        self::assertSame(SearchTypeEnum::Depth, $search->type);
    }
    // endregion METHOD_testSearchDepth

    // region METHOD_testCycleClause [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): CycleValueObject]
    /**
     * @purpose Verify Cycle value object stores columns, mark column, path column, and default values.
     */
    public function testCycleClause(): void
    {
        $cycle = new Cycle(
            ['id'],
            'is_loop',
            'path',
            new Literal(true),
            new Literal(false),
        );

        self::assertSame(['id'], $cycle->columns);
        self::assertSame('is_loop', $cycle->cycleMarkColumnName);
        self::assertSame('path', $cycle->cyclePathColumnName);
        self::assertTrue($cycle->cycleMarkValue->getExpression(new PgSqlGrammar()) !== '');
        self::assertCount(1, $cycle->cycleMarkValue->getParams());
    }
    // endregion METHOD_testCycleClause

    // region METHOD_testCycleDefaults [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): DefaultValues]
    /**
     * @purpose Verify Cycle has default mark values (true/false).
     */
    public function testCycleDefaults(): void
    {
        $cycle = new Cycle(['id'], 'is_loop', 'path');
        self::assertInstanceOf(Literal::class, $cycle->cycleMarkValue);
        self::assertInstanceOf(Literal::class, $cycle->cycleMarkDefault);
    }
    // endregion METHOD_testCycleDefaults

    // region METHOD_testWithQuerySearchMethod [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): FluentSearch]
    /**
     * @purpose Verify WithQuery::search() sets the search clause.
     */
    public function testWithQuerySearchMethod(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);

        $withQuery = new WithQuery($query);
        $withQuery->search(SearchTypeEnum::Breadth, ['id', 'parent_id'], 'seq');

        self::assertNotNull($withQuery->search);
        self::assertSame(SearchTypeEnum::Breadth, $withQuery->search->type);
    }
    // endregion METHOD_testWithQuerySearchMethod

    // region METHOD_testWithQueryCycleMethod [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): FluentCycle]
    /**
     * @purpose Verify WithQuery::cycle() sets the cycle clause.
     */
    public function testWithQueryCycleMethod(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);

        $withQuery = new WithQuery($query);
        $withQuery->cycle(['id'], 'is_loop', 'path');

        self::assertNotNull($withQuery->cycle);
        self::assertSame('is_loop', $withQuery->cycle->cycleMarkColumnName);
    }
    // endregion METHOD_testWithQueryCycleMethod

    // region METHOD_testPgSqlMaterialized [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): PgSqlMaterialized]
    /**
     * @purpose Verify PgSqlWithQuery materialized property.
     */
    public function testPgSqlMaterialized(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);

        $withQuery = new PgSqlWithQuery($query, materialized: true);
        self::assertTrue($withQuery->materialized);

        $withQuery2 = new PgSqlWithQuery($query, materialized: false);
        self::assertFalse($withQuery2->materialized);
    }
    // endregion METHOD_testPgSqlMaterialized

    // region METHOD_testPgSqlNotMaterialized [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): PgSqlNoMaterialize]
    /**
     * @purpose Verify PgSqlWithQuery without materialization hint.
     */
    public function testPgSqlNotMaterialized(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);

        $withQuery = new PgSqlWithQuery($query);
        self::assertNull($withQuery->materialized);
    }
    // endregion METHOD_testPgSqlNotMaterialized

    // region METHOD_testPgSqlWithQueryInGrammar [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): GrammarBuild]
    /**
     * @purpose Verify PgSqlGrammar builds CTE with materialization.
     */
    public function testPgSqlWithQueryInGrammar(): void
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
    // endregion METHOD_testPgSqlWithQueryInGrammar

    // region METHOD_testPgSqlWithSearchInGrammar [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): SearchInGrammar]
    /**
     * @purpose Verify PgSqlGrammar builds CTE with SEARCH clause.
     */
    public function testPgSqlWithSearchInGrammar(): void
    {
        $grammar = new PgSqlGrammar();

        $cteQuery = new SelectQuery();
        $cteQuery->select(['id', 'parent_id'])->from(['tree']);

        $withQuery = new WithQuery($cteQuery);
        $withQuery->search(SearchTypeEnum::Breadth, ['id', 'parent_id'], 'seq');

        $mainQuery = new SelectQuery();
        $mainQuery->select(['id'])->from(['tree_cte']);
        $mainQuery->with(['tree_cte' => $withQuery]);

        $built = $grammar->buildSelectQuery($mainQuery);
        self::assertSame(
            'WITH "tree_cte" AS ( SELECT "id", "parent_id" FROM "tree" ) SEARCH BREADTH FIRST BY "id", "parent_id" SET "seq" SELECT "id" FROM "tree_cte"',
            $built->sql,
        );
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlWithSearchInGrammar

    // region METHOD_testPgSqlWithSearchAndCycleTogether [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): SearchCycleTogether]
    /**
     * @purpose Verify PgSqlGrammar builds CTE with both SEARCH and CYCLE.
     */
    public function testPgSqlWithSearchAndCycleTogether(): void
    {
        $grammar = new PgSqlGrammar();

        $cteQuery = new SelectQuery();
        $cteQuery->select(['id', 'parent_id'])->from(['tree']);

        $withQuery = new WithQuery($cteQuery);
        $withQuery->search(SearchTypeEnum::Breadth, ['id', 'parent_id'], 'seq');
        $withQuery->cycle(['id'], 'is_loop', 'path');

        $mainQuery = new SelectQuery();
        $mainQuery->select(['id'])->from(['tree_cte']);
        $mainQuery->with(['tree_cte' => $withQuery]);

        $built = $grammar->buildSelectQuery($mainQuery);
        self::assertMatchesRegularExpression(
            '/^WITH "tree_cte" AS \( SELECT "id", "parent_id" FROM "tree" \) SEARCH BREADTH FIRST BY "id", "parent_id" SET "seq" CYCLE "id" SET "is_loop" TO :v\d+_\d+ DEFAULT :v\d+_\d+ USING "is_loop" SELECT "id" FROM "tree_cte"$/',
            $built->sql,
        );
        self::assertCount(2, $built->params);
    }
    // endregion METHOD_testPgSqlWithSearchAndCycleTogether

    // region METHOD_testPgSqlNotMaterializedInGrammar [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): NotMaterialized]
    /**
     * @purpose Verify PgSqlGrammar builds CTE with NOT MATERIALIZED.
     */
    public function testPgSqlNotMaterializedInGrammar(): void
    {
        $grammar = new PgSqlGrammar();

        $cteQuery = new SelectQuery();
        $cteQuery->select(['id'])->from(['users']);

        $mainQuery = new SelectQuery();
        $mainQuery->select(['id'])->from(['active_users']);
        $mainQuery->with(['active_users' => new PgSqlWithQuery($cteQuery, materialized: false)]);

        $built = $grammar->buildSelectQuery($mainQuery);
        self::assertSame('WITH "active_users" AS NOT MATERIALIZED ( SELECT "id" FROM "users" ) SELECT "id" FROM "active_users"', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testPgSqlNotMaterializedInGrammar

    // region METHOD_testPgSqlWithCycleInGrammar [DOMAIN(9): Testing; CONCEPT(9): CTE; TECH(9): CycleInGrammar]
    /**
     * @purpose Verify PgSqlGrammar builds CTE with CYCLE clause.
     */
    public function testPgSqlWithCycleInGrammar(): void
    {
        $grammar = new PgSqlGrammar();

        $cteQuery = new SelectQuery();
        $cteQuery->select(['id', 'parent_id'])->from(['tree']);

        $withQuery = new WithQuery($cteQuery);
        $withQuery->cycle(['id'], 'is_loop', 'path');

        $mainQuery = new SelectQuery();
        $mainQuery->select(['id'])->from(['tree_cte']);
        $mainQuery->with(['tree_cte' => $withQuery]);

        $built = $grammar->buildSelectQuery($mainQuery);
        self::assertMatchesRegularExpression(
            '/^WITH "tree_cte" AS \( SELECT "id", "parent_id" FROM "tree" \) CYCLE "id" SET "is_loop" TO :v\d+_\d+ DEFAULT :v\d+_\d+ USING "is_loop" SELECT "id" FROM "tree_cte"$/',
            $built->sql,
        );
        self::assertCount(2, $built->params);
    }
    // endregion METHOD_testPgSqlWithCycleInGrammar
}
// endregion CLASS_CteTest
