<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\Merge\MergeActionUpdate;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenMatchedClause;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenNotMatchedBySourceClause;
use AndrewGos\QueryBuilder\Expr\Merge\MergeWhenNotMatchedClause;
use AndrewGos\QueryBuilder\Expr\Merge\PgSql\PgSqlMergeActionDoNothing;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\Default\DefaultGrammar;
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Merge\MergeQuery;
use AndrewGos\QueryBuilder\Query\Merge\PgSql\PgSqlMergeQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Values\ValuesQuery;
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

    // region METHOD_testInsertWithTypedValues [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): ValueTypes]
    /**
     * @purpose Verify MergeActionInsert handles int, float, bool, null values correctly — scalars become bound params, bool/null are SQL literals.
     */
    public function testInsertWithTypedValues(): void
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
                MergeWhenMatchedClause::update(['name' => 's.name']),
            )
            ->whenNotMatched(
                MergeWhenNotMatchedClause::insert([
                    'id' => 's.id',
                    'count' => 42,
                    'ratio' => 3.14,
                    'active' => true,
                    'notes' => null,
                ]),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertStringContainsString(
            'WHEN NOT MATCHED THEN INSERT ("id", "count", "ratio", "active", "notes") VALUES ("s"."id", ',
            $built->sql,
        );
        self::assertStringContainsString('TRUE', $built->sql);
        self::assertStringContainsString('NULL', $built->sql);
        self::assertCount(2, $built->params);
    }
    // endregion METHOD_testInsertWithTypedValues

    // region METHOD_testUpdateSetWithSelectQuery [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): SubqueryValue]
    /**
     * @purpose Verify MergeActionUpdate accepts SelectQueryInterface as a SET value, rendering it as a subquery.
     */
    public function testUpdateSetWithSelectQuery(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $subquery = new SelectQuery()->select(['name'])->from(['source'])->where(['id' => 1]);

        $query = new MergeQuery();
        $query->into('target', 't')
            ->using('source', 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => $subquery]),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertStringContainsString(
            'WHEN MATCHED THEN UPDATE SET "name" = (SELECT "name" FROM "source" WHERE "id" = :',
            $built->sql,
        );
        self::assertCount(1, $built->params);
    }
    // endregion METHOD_testUpdateSetWithSelectQuery

    // region METHOD_testUpdateSetWithValuesQuery [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): ValuesQueryValue]
    /**
     * @purpose Verify MergeActionUpdate accepts ValuesQueryInterface as a SET value, rendering it as a subquery.
     */
    public function testUpdateSetWithValuesQuery(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $values = new ValuesQuery()->values([['new_name']]);

        $query = new MergeQuery();
        $query->into('target', 't')
            ->using('source', 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => $values]),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertStringContainsString(
            'WHEN MATCHED THEN UPDATE SET "name" = (VALUES (:',
            $built->sql,
        );
        self::assertCount(1, $built->params);
    }
    // endregion METHOD_testUpdateSetWithValuesQuery

    // region METHOD_testUpdateSetWithPrebuiltSetClause [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): SetClause]
    /**
     * @purpose Verify MergeActionUpdate accepts a pre-built array of SetClause objects.
     */
    public function testUpdateSetWithPrebuiltSetClause(): void
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
                new MergeWhenMatchedClause(
                    new MergeActionUpdate([new SetClause('name', 's.name')]),
                ),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING "source" AS "s" ON "t"."id" = "s"."id" WHEN MATCHED THEN UPDATE SET "name" = "s"."name"',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testUpdateSetWithPrebuiltSetClause

    // region METHOD_testUsingWithValuesQuery [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): UsingValues]
    /**
     * @purpose Verify USING clause accepts a ValuesQuery as source.
     */
    public function testUsingWithValuesQuery(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $values = new ValuesQuery()->values([[1, 'Alice']]);

        $query = new MergeQuery();
        $query->into('target', 't')
            ->using($values, 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => 's.name']),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertStringContainsString('USING (VALUES (', $built->sql);
        self::assertStringContainsString('AS "s"', $built->sql);
    }
    // endregion METHOD_testUsingWithValuesQuery

    // region METHOD_testUsingWithExpr [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): UsingExpr]
    /**
     * @purpose Verify USING clause accepts an ExprInterface (raw expression) as source.
     */
    public function testUsingWithExpr(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new MergeQuery();
        $query->into('target', 't')
            ->using(new Expr('raw_source'), 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => 's.name']),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING raw_source AS "s" ON "t"."id" = "s"."id" WHEN MATCHED THEN UPDATE SET "name" = "s"."name"',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testUsingWithExpr

    // region METHOD_testDefaultGrammarMerge [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): DefaultGrammar]
    /**
     * @purpose Verify that DefaultGrammar (concrete ANSI class) builds a basic MERGE query correctly.
     */
    public function testDefaultGrammarMerge(): void
    {
        $grammar = new DefaultGrammar();

        $query = new MergeQuery();
        $query->into('target', 't')
            ->using('source', 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => 's.name']),
            )
            ->whenNotMatched(
                MergeWhenNotMatchedClause::insert(['id' => 's.id', 'name' => 's.name']),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING "source" AS "s" ON "t"."id" = "s"."id" WHEN MATCHED THEN UPDATE SET "name" = "s"."name" WHEN NOT MATCHED THEN INSERT ("id", "name") VALUES ("s"."id", "s"."name")',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testDefaultGrammarMerge

    // region METHOD_testMergeWithoutWhenClauses [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): EdgeCase]
    /**
     * @purpose Verify that MERGE without any WHEN clauses produces only MERGE INTO ... USING ... ON (no action clauses).
     */
    public function testMergeWithoutWhenClauses(): void
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
            ->on(['t.id' => 's.id']);

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING "source" AS "s" ON "t"."id" = "s"."id"',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testMergeWithoutWhenClauses

    // region METHOD_testMultipleBySourceClauses [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): BySource]
    /**
     * @purpose Verify multiple WHEN NOT MATCHED BY SOURCE clauses are rendered in order.
     */
    public function testMultipleBySourceClauses(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlMergeQuery();
        $query->into('target', 't')
            ->using('source', 's')
            ->on(['t.id' => 's.id'])
            ->whenNotMatchedBySource(
                MergeWhenNotMatchedBySourceClause::update(
                    ['status' => new Expr("'archived'")],
                    ['t.active' => true],
                ),
            )
            ->whenNotMatchedBySource(
                MergeWhenNotMatchedBySourceClause::delete(),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "target" AS "t" USING "source" AS "s" ON "t"."id" = "s"."id" WHEN NOT MATCHED BY SOURCE AND "t"."active" IS TRUE THEN UPDATE SET "status" = \'archived\' WHEN NOT MATCHED BY SOURCE THEN DELETE',
            $built->sql,
        );
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testMultipleBySourceClauses

    // region METHOD_testOnWithExpr [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): OnExpr]
    /**
     * @purpose Verify ON clause accepts ExprInterface values (raw expressions) alongside string column references.
     */
    public function testOnWithExpr(): void
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
            ->on(['t.id' => new Expr('s.id')])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => 's.name']),
            );

        $built = $grammar->buildMergeQuery($query);

        // Justification: Expr values in OpExpr are parenthesized (ExprInterface not instanceof ColumnExpr), so we verify structural content.
        self::assertStringContainsString('"t"."id" = (s.id)', $built->sql);
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testOnWithExpr

    // region METHOD_testPgSqlMergeWithoutReturning [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): PgSqlNoReturning]
    /**
     * @purpose Verify PgSql MERGE without RETURNING clause still produces valid SQL and isReturnable() returns false.
     */
    public function testPgSqlMergeWithoutReturning(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlMergeQuery();
        $query->into('users', 't')
            ->using('staging', 's')
            ->on(['t.id' => 's.id'])
            ->whenMatched(
                MergeWhenMatchedClause::update(['name' => 's.name']),
            );

        $built = $grammar->buildMergeQuery($query);

        self::assertSame(
            'MERGE INTO "users" AS "t" USING "staging" AS "s" ON "t"."id" = "s"."id" WHEN MATCHED THEN UPDATE SET "name" = "s"."name"',
            $built->sql,
        );
        self::assertEmpty($built->params);
        self::assertFalse($query->isReturnable());
    }
    // endregion METHOD_testPgSqlMergeWithoutReturning
}
// endregion CLASS_MergeQueryTest
