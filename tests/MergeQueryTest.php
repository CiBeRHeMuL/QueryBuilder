<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenMatchedClause;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenNotMatchedBySourceClause;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenNotMatchedClause;
use AndrewGos\QueryBuilder\Expr\Merge\PgSql\PgSqlMergeActionDoNothing;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Merge\MergeQuery;
use AndrewGos\QueryBuilder\Query\Merge\PgSql\PgSqlMergeQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_MergeQueryTest [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): SQLGeneration]
/**
 * @purpose Test MERGE query building across ANSI and PostgreSQL dialects. Covers basic MERGE, AND conditions, DELETE, subquery USING, BY SOURCE, DO NOTHING, RETURNING, CTE.
 */
class MergeQueryTest extends TestCase
{
    // region METHOD_testAnsiBasicMerge [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): ANSI]
    /**
     * @purpose Verify AbstractGrammar builds basic ANSI MERGE: MERGE INTO target USING source ON condition WHEN MATCHED THEN UPDATE SET WHEN NOT MATCHED THEN INSERT.
     */
    public function testAnsiBasicMerge(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new MergeQuery();
        $query->into('target', 't')
            ->using('source', 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => 's.name', 'email' => 's.email']),
            )
            ->whenNotMatched(
                MergeWhenNotMatchedClause::insert(
                    ['id' => 's.id', 'name' => 's.name', 'email' => 's.email'],
                ),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING "source" AS "s" ON "t"."id" = "s"."id" WHEN MATCHED THEN UPDATE SET "name" = "s"."name", "email" = "s"."email" WHEN NOT MATCHED THEN INSERT ("id", "name", "email") VALUES ("s"."id", "s"."name", "s"."email")',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testAnsiBasicMerge

    // region METHOD_testAnsiMergeWithAndDelete [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): ANSI]
    /**
     * @purpose Verify ANSI MERGE with AND conditions and DELETE action.
     */
    public function testAnsiMergeWithAndDelete(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new MergeQuery();
        $query->into('target', 't')
            ->using('source', 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => 's.name'], ['t.locked' => false]),
            )
            ->whenMatched(
                MergeWhenMatchedClause::delete(['s.status' => new Expr("'deleted'")]),
            )
            ->whenNotMatched(
                MergeWhenNotMatchedClause::insert(
                    ['id' => 's.id', 'name' => 's.name'],
                ),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING "source" AS "s" ON "t"."id" = "s"."id" WHEN MATCHED AND "t"."locked" IS FALSE THEN UPDATE SET "name" = "s"."name" WHEN MATCHED AND "s"."status" = (\'deleted\') THEN DELETE WHEN NOT MATCHED THEN INSERT ("id", "name") VALUES ("s"."id", "s"."name")',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testAnsiMergeWithAndDelete

    // region METHOD_testAnsiMergeWithSubqueryUsing [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): Subquery]
    /**
     * @purpose Verify ANSI MERGE with a SELECT subquery in USING.
     */
    public function testAnsiMergeWithSubqueryUsing(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $subquery = new SelectQuery();
        $subquery->select(['id', 'name'])->from(['source_table']);

        $query = new MergeQuery();
        $query->into('target', 't')
            ->using($subquery, 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => 's.name']),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING (SELECT "id", "name" FROM "source_table") AS "s" ON "t"."id" = "s"."id" WHEN MATCHED THEN UPDATE SET "name" = "s"."name"',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testAnsiMergeWithSubqueryUsing

    // region METHOD_testPgSqlMergeReturning [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): PgSql]
    /**
     * @purpose Verify PgSqlGrammar builds MERGE with RETURNING clause.
     */
    public function testPgSqlMergeReturning(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlMergeQuery();
        $query->into('users', 't')
            ->using('staging', 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => 's.name']),
            )
            ->whenNotMatched(
                MergeWhenNotMatchedClause::insert(
                    ['id' => 's.id', 'name' => 's.name'],
                ),
            )
            ->returning(['t.id', 't.name']);

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "users" AS "t" USING "staging" AS "s" ON "t"."id" = "s"."id" WHEN MATCHED THEN UPDATE SET "name" = "s"."name" WHEN NOT MATCHED THEN INSERT ("id", "name") VALUES ("s"."id", "s"."name") RETURNING "t"."id", "t"."name"',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testPgSqlMergeReturning

    // region METHOD_testPgSqlMergeFullPipeline [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): FullPipeline]
    /**
     * @purpose Verify PgSqlGrammar builds full MERGE pipeline: CTE + WHEN MATCHED (UPDATE, DELETE) + WHEN NOT MATCHED (INSERT, DO NOTHING) + BY SOURCE DELETE + RETURNING.
     */
    public function testPgSqlMergeFullPipeline(): void
    {
        $grammar = new PgSqlGrammar();

        $staging = new SelectQuery();
        $staging->select(['id', 'name', 'status'])->from(['raw_import']);

        $query = new PgSqlMergeQuery();
        $query->with(['staging' => new WithQuery($staging)])
            ->into('users', 't')
            ->using('staging', 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(
                    ['name' => 's.name', 'email' => 's.email'],
                    ['t.locked' => false],
                ),
            )
            ->whenMatched(
                MergeWhenMatchedClause::delete(['s.status' => new Expr("'deleted'")]),
            )
            ->whenNotMatched(
                MergeWhenNotMatchedClause::insert(
                    ['id' => 's.id', 'name' => 's.name', 'email' => 's.email', 'status' => new Expr("'active'")],
                ),
            )
            ->whenNotMatched(
                new MergeWhenNotMatchedClause(
                    new PgSqlMergeActionDoNothing(),
                    ['s.status' => 'skip'],
                ),
            )
            ->whenNotMatchedBySource(
                MergeWhenNotMatchedBySourceClause::delete(),
            )
            ->returning(['t.id', 't.name', 't.status']);

        $built = $grammar->buildMergeQuery($query);

        // Justification: param name `:v{n}_{m}` is auto-generated by ValueBuilder (non-deterministic across runs).
        // We verify structural content instead of exact full SQL.
        self::assertStringContainsString('WITH "staging" AS ( SELECT "id", "name", "status" FROM "raw_import" ) MERGE INTO "users" AS "t"', $built->sql);
        self::assertStringContainsString('WHEN MATCHED AND "t"."locked" IS FALSE THEN UPDATE SET "name" = "s"."name", "email" = "s"."email"', $built->sql);
        self::assertStringContainsString("WHEN MATCHED AND \"s\".\"status\" = ('deleted') THEN DELETE", $built->sql);
        self::assertStringContainsString('WHEN NOT MATCHED THEN INSERT ("id", "name", "email", "status") VALUES ("s"."id", "s"."name", "s"."email", \'active\')', $built->sql);
        self::assertStringContainsString('WHEN NOT MATCHED AND "s"."status" = :', $built->sql);
        self::assertStringContainsString('THEN DO NOTHING', $built->sql);
        self::assertStringContainsString('WHEN NOT MATCHED BY SOURCE THEN DELETE', $built->sql);
        self::assertStringContainsString('RETURNING "t"."id", "t"."name", "t"."status"', $built->sql);
        self::assertCount(1, $built->params);
        self::assertSame('skip', $built->params[array_key_first($built->params)]);
    }
    // endregion METHOD_testPgSqlMergeFullPipeline

    // region METHOD_testPgSqlMergeDoNothingBySourceUpdate [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): BySource]
    /**
     * @purpose Verify BY SOURCE UPDATE with AND condition and DO NOTHING as action.
     */
    public function testPgSqlMergeDoNothingBySourceUpdate(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlMergeQuery();
        $query->into('target', 't')
            ->using('source', 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => 's.name']),
            )
            ->whenNotMatchedBySource(
                MergeWhenNotMatchedBySourceClause::update(['status' => new Expr("'archived'")], ['t.active' => true]),
            );

        $query->whenNotMatchedBySource(
            new MergeWhenNotMatchedBySourceClause(new PgSqlMergeActionDoNothing()),
        );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING "source" AS "s" ON "t"."id" = "s"."id" WHEN MATCHED THEN UPDATE SET "name" = "s"."name" WHEN NOT MATCHED BY SOURCE AND "t"."active" IS TRUE THEN UPDATE SET "status" = \'archived\' WHEN NOT MATCHED BY SOURCE THEN DO NOTHING',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testPgSqlMergeDoNothingBySourceUpdate

    // region METHOD_testPgSqlMergeBySourceDelete [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): BySource]
    /**
     * @purpose Verify simple BY SOURCE DELETE without AND conditions.
     */
    public function testPgSqlMergeBySourceDelete(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlMergeQuery();
        $query->into('target', 't')
            ->using('source', 's')
            ->on(['t.id' => 's.id'])
            ->whenNotMatchedBySource(
                MergeWhenNotMatchedBySourceClause::delete(),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING "source" AS "s" ON "t"."id" = "s"."id" WHEN NOT MATCHED BY SOURCE THEN DELETE',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testPgSqlMergeBySourceDelete

    // region METHOD_testAnsiMergeBySource [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): ANSI]
    /**
     * @purpose Verify that AbstractGrammar renders BY SOURCE clauses (WHEN NOT MATCHED BY SOURCE is part of ANSI SQL:2008).
     */
    public function testAnsiMergeBySource(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new MergeQuery();
        $query->into('target', 't')
            ->using('source', 's')
            ->on(['t.id' => 's.id'])
            ->whenNotMatchedBySource(
                MergeWhenNotMatchedBySourceClause::delete(),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING "source" AS "s" ON "t"."id" = "s"."id" WHEN NOT MATCHED BY SOURCE THEN DELETE',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testAnsiMergeBySource

    // region METHOD_testPgSqlMergeBySourceWithAnd [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): BySource]
    /**
     * @purpose Verify BY SOURCE with AND condition: WHEN NOT MATCHED BY SOURCE AND cond THEN UPDATE SET.
     */
    public function testPgSqlMergeBySourceWithAnd(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlMergeQuery();
        $query->into('target', 't')
            ->using('source', 's')
            ->on(['t.id' => 's.id'])
            ->whenNotMatchedBySource(
                MergeWhenNotMatchedBySourceClause::update(['name' => 's.name'], ['t.active' => true]),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING "source" AS "s" ON "t"."id" = "s"."id" WHEN NOT MATCHED BY SOURCE AND "t"."active" IS TRUE THEN UPDATE SET "name" = "s"."name"',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testPgSqlMergeBySourceWithAnd

    // region METHOD_testMySqlGrammarThrows [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): MySQL]
    /**
     * @purpose Verify that MySqlGrammar throws QueryBuilderException for MERGE queries.
     */
    public function testMySqlGrammarThrows(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MergeQuery();
        $query->into('target')
            ->using('source')
            ->on(['t.id' => 's.id']);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('MERGE is not supported');

        $grammar->buildMergeQuery($query);
    }
    // endregion METHOD_testMySqlGrammarThrows

    // region METHOD_testMergeValidationNoInto
    /**
     * @purpose Verify that building MERGE without into() throws.
     */
    public function testMergeValidationNoInto(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new MergeQuery();
        $query->using('source')
            ->on(['t.id' => 's.id']);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('target table');

        $grammar->buildMergeQuery($query);
    }
    // endregion METHOD_testMergeValidationNoInto

    // region METHOD_testPgSqlMergeIsReturnable [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): Returnable]
    /**
     * @purpose Verify PgSqlMergeQuery implements MaybeReturnableQueryInterface and isReturnable() returns true only when RETURNING is set.
     */
    public function testPgSqlMergeIsReturnable(): void
    {
        $query = new PgSqlMergeQuery();
        self::assertInstanceOf(MaybeReturnableQueryInterface::class, $query);
        self::assertFalse($query->isReturnable());

        $query->returning(['id']);
        self::assertTrue($query->isReturnable());
    }
    // endregion METHOD_testPgSqlMergeIsReturnable
}
// endregion CLASS_MergeQueryTest
