<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Expr\Conflict\PgSql\PgSqlConflictActionDoNothing;
use AndrewGos\QueryBuilder\Expr\Conflict\PgSql\PgSqlConflictActionDoUpdate;
use AndrewGos\QueryBuilder\Expr\Conflict\PgSql\PgSqlConflictTargetColumns;
use AndrewGos\QueryBuilder\Expr\Conflict\PgSql\PgSqlConflictTargetConstraint;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Query\Insert\MySql\MySqlInsertQuery;
use AndrewGos\QueryBuilder\Query\Insert\PgSql\PgSqlInsertQuery;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Values\ValuesQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_InsertQueryTest [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): SQLGeneration]
/**
 * @purpose Test INSERT query building across all dialects: AbstractGrammar, PgSql, MySql.
 */
class InsertQueryTest extends TestCase
{
    // region METHOD_testAbstractGrammarDefaultValues [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): DefaultValues]
    /**
     * @purpose Verify AbstractGrammar builds INSERT INTO table DEFAULT VALUES.
     */
    public function testAbstractGrammarDefaultValues(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new \AndrewGos\QueryBuilder\Query\Insert\InsertQuery();
        $query->into('users');

        $built = $grammar->buildInsertQuery($query);

        self::assertSame('INSERT INTO "users" DEFAULT VALUES', $built->sql);
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testAbstractGrammarDefaultValues

    // region METHOD_testAbstractGrammarWithValues [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): ValuesSource]
    /**
     * @purpose Verify AbstractGrammar builds INSERT INTO table (columns) VALUES (...).
     */
    public function testAbstractGrammarWithValues(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $values = new ValuesQuery();
        $values->values([[1, 'Alice']]);
        $query = new \AndrewGos\QueryBuilder\Query\Insert\InsertQuery();
        $query->into('users', ['id', 'name']);
        $query->source($values);

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('INSERT INTO "users" ("id", "name")', $built->sql);
        self::assertStringContainsString('VALUES', $built->sql);
        self::assertNotEmpty($built->params);
        self::assertCount(2, $built->params);
    }
    // endregion METHOD_testAbstractGrammarWithValues

    // region METHOD_testAbstractGrammarWithAlias [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): TableAlias]
    /**
     * @purpose Verify AbstractGrammar builds INSERT INTO table AS alias with column list.
     */
    public function testAbstractGrammarWithAlias(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };

        $query = new \AndrewGos\QueryBuilder\Query\Insert\InsertQuery();
        $query->into('users', ['id', 'name'], 'u');

        $built = $grammar->buildInsertQuery($query);

        self::assertSame('INSERT INTO "users" AS "u" ("id", "name") DEFAULT VALUES', $built->sql);
    }
    // endregion METHOD_testAbstractGrammarWithAlias

    // region METHOD_testPgSqlOverrideValue [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): OverrideValue]
    /**
     * @purpose Verify PgSqlGrammar builds INSERT ... OVERRIDING USER VALUE.
     */
    public function testPgSqlOverrideValue(): void
    {
        $grammar = new PgSqlGrammar();
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->overrideValue(\AndrewGos\QueryBuilder\Enum\Insert\PgSql\PgSqlOverrideValueMethodEnum::User);

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('OVERRIDING USER VALUE', $built->sql);
        self::assertStringContainsString('INSERT INTO', $built->sql);
    }
    // endregion METHOD_testPgSqlOverrideValue

    // region METHOD_testPgSqlOnConflictDoNothing [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): OnConflict]
    /**
     * @purpose Verify PgSqlGrammar builds INSERT ... ON CONFLICT DO NOTHING.
     */
    public function testPgSqlOnConflictDoNothing(): void
    {
        $grammar = new PgSqlGrammar();
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id']);
        $query->onConflict(new PgSqlConflictActionDoNothing());

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('ON CONFLICT DO NOTHING', $built->sql);
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testPgSqlOnConflictDoNothing

    // region METHOD_testPgSqlOnConflictDoNothingWithTarget [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): OnConflict]
    /**
     * @purpose Verify PgSqlGrammar builds INSERT ... ON CONFLICT (id) DO NOTHING.
     */
    public function testPgSqlOnConflictDoNothingWithTarget(): void
    {
        $grammar = new PgSqlGrammar();
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->onConflict(
            new PgSqlConflictActionDoNothing(),
            new PgSqlConflictTargetColumns(['id']),
        );

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('ON CONFLICT ("id") DO NOTHING', $built->sql);
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testPgSqlOnConflictDoNothingWithTarget

    // region METHOD_testPgSqlOnConflictDoUpdate [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): OnConflict]
    /**
     * @purpose Verify PgSqlGrammar builds INSERT ... ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name.
     */
    public function testPgSqlOnConflictDoUpdate(): void
    {
        $grammar = new PgSqlGrammar();
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->onConflict(
            new PgSqlConflictActionDoUpdate(
                ['name' => new Expr('EXCLUDED.name')],
            ),
            new PgSqlConflictTargetColumns(['id']),
        );

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('ON CONFLICT ("id")', $built->sql);
        self::assertStringContainsString('DO UPDATE SET "name" = EXCLUDED.name', $built->sql);
    }
    // endregion METHOD_testPgSqlOnConflictDoUpdate

    // region METHOD_testPgSqlOnConflictDoUpdateWithWhere [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): OnConflict]
    /**
     * @purpose Verify PgSqlGrammar builds INSERT ... ON CONFLICT DO UPDATE SET col = val WHERE condition.
     */
    public function testPgSqlOnConflictDoUpdateWithWhere(): void
    {
        $grammar = new PgSqlGrammar();
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->onConflict(
            new PgSqlConflictActionDoUpdate(
                ['name' => 'Bob'],
                ['users.active' => true],
            ),
            new PgSqlConflictTargetColumns(['id']),
        );

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('ON CONFLICT ("id")', $built->sql);
        self::assertStringContainsString('DO UPDATE SET', $built->sql);
        self::assertStringContainsString('WHERE', $built->sql);
        self::assertNotEmpty($built->params);
    }
    // endregion METHOD_testPgSqlOnConflictDoUpdateWithWhere

    // region METHOD_testPgSqlOnConflictTargetConstraint [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): OnConflict]
    /**
     * @purpose Verify PgSqlGrammar builds INSERT ... ON CONFLICT ON CONSTRAINT constraint_name DO NOTHING.
     */
    public function testPgSqlOnConflictTargetConstraint(): void
    {
        $grammar = new PgSqlGrammar();
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id']);
        $query->onConflict(
            new PgSqlConflictActionDoNothing(),
            new PgSqlConflictTargetConstraint('users_pkey'),
        );

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('ON CONFLICT ON CONSTRAINT "users_pkey" DO NOTHING', $built->sql);
        self::assertEmpty($built->params);
    }
    // endregion METHOD_testPgSqlOnConflictTargetConstraint

    // region METHOD_testPgSqlReturning [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): Returning]
    /**
     * @purpose Verify PgSqlGrammar builds INSERT ... RETURNING id.
     */
    public function testPgSqlReturning(): void
    {
        $grammar = new PgSqlGrammar();
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->returning(['id']);

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('RETURNING "id"', $built->sql);
    }
    // endregion METHOD_testPgSqlReturning

    // region METHOD_testPgSqlOnConflictWithReturning [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): FullFeature]
    /**
     * @purpose Verify PgSqlGrammar builds full INSERT ... ON CONFLICT DO UPDATE ... RETURNING.
     */
    public function testPgSqlOnConflictWithReturning(): void
    {
        $grammar = new PgSqlGrammar();
        $query = new PgSqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->onConflict(
            new PgSqlConflictActionDoUpdate(['name' => new Expr('EXCLUDED.name')]),
            new PgSqlConflictTargetColumns(['id']),
        );
        $query->returning(['id']);

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('ON CONFLICT ("id") DO UPDATE SET "name" = EXCLUDED.name', $built->sql);
        self::assertStringContainsString('RETURNING "id"', $built->sql);
    }
    // endregion METHOD_testPgSqlOnConflictWithReturning

    // region METHOD_testPgSqlInsertIsReturnable [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): Returnable]
    /**
     * @purpose Verify PgSqlInsertQuery implements MaybeReturnableQueryInterface and isReturnable() returns true only when RETURNING is set.
     */
    public function testPgSqlInsertIsReturnable(): void
    {
        $query = new PgSqlInsertQuery();
        self::assertInstanceOf(MaybeReturnableQueryInterface::class, $query);
        self::assertFalse($query->isReturnable());

        $query->returning(['id']);
        self::assertTrue($query->isReturnable());
    }
    // endregion METHOD_testPgSqlInsertIsReturnable

    // region METHOD_testPgSqlInsertAddReturning [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): Returning]
    /**
     * @purpose Verify PgSqlInsertQuery::addReturning() merges columns and keeps isReturnable true.
     */
    public function testPgSqlInsertAddReturning(): void
    {
        $query = new PgSqlInsertQuery();
        $query->returning(['id']);
        $query->addReturning(['name']);

        self::assertTrue($query->isReturnable());
        self::assertNotNull($query->returningColumns);
        self::assertCount(2, $query->returningColumns);
        self::assertSame(['id', 'name'], $query->returningColumns);
    }
    // endregion METHOD_testPgSqlInsertAddReturning

    // region METHOD_testPgSqlInsertWithReturningAndWith [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): CteReturning]
    /**
     * @purpose Verify PgSqlGrammar builds WITH + INSERT ... RETURNING correctly.
     */
    public function testPgSqlInsertWithReturningAndWith(): void
    {
        $grammar = new PgSqlGrammar();

        $cteQuery = new SelectQuery();
        $cteQuery->select(['id', 'name'])->from(['pending_users'])->where(['status' => 'active']);

        $values = new ValuesQuery();
        $values->values([[1, 'Alice'], [2, 'Bob']]);

        $query = new PgSqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->source($values);
        $query->with(['pending' => new WithQuery($cteQuery)]);
        $query->returning(['id']);

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('WITH "pending" AS', $built->sql);
        self::assertStringContainsString('INSERT INTO "users"', $built->sql);
        self::assertStringContainsString('RETURNING "id"', $built->sql);
    }
    // endregion METHOD_testPgSqlInsertWithReturningAndWith

    // region METHOD_testPgSqlInsertWithSelectReturning [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): SelectSourceReturning]
    /**
     * @purpose Verify PgSqlGrammar builds INSERT ... SELECT ... RETURNING correctly.
     */
    public function testPgSqlInsertWithSelectReturning(): void
    {
        $grammar = new PgSqlGrammar();

        $source = new SelectQuery();
        $source->select(['id', 'name'])->from(['tmp_users']);

        $query = new PgSqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->source($source);
        $query->returning(['id', 'name']);

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('INSERT INTO "users" ("id", "name")', $built->sql);
        self::assertStringContainsString('SELECT "id", "name" FROM "tmp_users"', $built->sql);
        self::assertStringContainsString('RETURNING "id", "name"', $built->sql);
    }
    // endregion METHOD_testPgSqlInsertWithSelectReturning

    // region METHOD_testPgSqlInsertOnConflictWithReturningAndWith [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): FullPipeline]
    /**
     * @purpose Verify PgSqlGrammar builds WITH + INSERT ... ON CONFLICT DO UPDATE ... RETURNING — full PostgreSQL pipeline.
     */
    public function testPgSqlInsertOnConflictWithReturningAndWith(): void
    {
        $grammar = new PgSqlGrammar();

        $cteQuery = new SelectQuery();
        $cteQuery->select(['id', 'name'])->from(['staging_users']);

        $values = new ValuesQuery();
        $values->values([[1, 'Alice']]);

        $query = new PgSqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->source($values);
        $query->with(['staging' => new WithQuery($cteQuery)]);
        $query->onConflict(
            new PgSqlConflictActionDoUpdate(['name' => new Expr('EXCLUDED.name')]),
            new PgSqlConflictTargetColumns(['id']),
        );
        $query->returning(['id', 'name']);

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('WITH "staging" AS', $built->sql);
        self::assertStringContainsString('ON CONFLICT ("id") DO UPDATE SET "name" = EXCLUDED.name', $built->sql);
        self::assertStringContainsString('RETURNING "id", "name"', $built->sql);
    }
    // endregion METHOD_testPgSqlInsertOnConflictWithReturningAndWith

    // region METHOD_testMySqlLowPriority [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): MySQLModifier]
    /**
     * @purpose Verify MySqlGrammar builds INSERT LOW_PRIORITY INTO table.
     */
    public function testMySqlLowPriority(): void
    {
        $grammar = new MySqlGrammar();
        $query = new MySqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->lowPriority();

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('INSERT LOW_PRIORITY INTO', $built->sql);
        self::assertStringContainsString('`users`', $built->sql);
    }
    // endregion METHOD_testMySqlLowPriority

    // region METHOD_testMySqlIgnore [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): MySQLModifier]
    /**
     * @purpose Verify MySqlGrammar builds INSERT IGNORE INTO table.
     */
    public function testMySqlIgnore(): void
    {
        $grammar = new MySqlGrammar();
        $query = new MySqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->ignore();

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('INSERT IGNORE INTO', $built->sql);
    }
    // endregion METHOD_testMySqlIgnore

    // region METHOD_testMySqlHighPriority [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): MySQLModifier]
    /**
     * @purpose Verify MySqlGrammar builds INSERT HIGH_PRIORITY INTO table.
     */
    public function testMySqlHighPriority(): void
    {
        $grammar = new MySqlGrammar();
        $query = new MySqlInsertQuery();
        $query->into('users');
        $query->highPriority();

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('INSERT HIGH_PRIORITY INTO', $built->sql);
    }
    // endregion METHOD_testMySqlHighPriority

    // region METHOD_testMySqlDelayed [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): MySQLModifier]
    /**
     * @purpose Verify MySqlGrammar builds INSERT DELAYED INTO table.
     */
    public function testMySqlDelayed(): void
    {
        $grammar = new MySqlGrammar();
        $query = new MySqlInsertQuery();
        $query->into('users');
        $query->delayed();

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('INSERT DELAYED INTO', $built->sql);
    }
    // endregion METHOD_testMySqlDelayed

    // region METHOD_testMySqlPartition [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): Partition]
    /**
     * @purpose Verify MySqlGrammar builds INSERT INTO table PARTITION (p1).
     */
    public function testMySqlPartition(): void
    {
        $grammar = new MySqlGrammar();
        $query = new MySqlInsertQuery();
        $query->into('users', ['id', 'name']);
        $query->partition(['p1']);

        $built = $grammar->buildInsertQuery($query);

        self::assertStringContainsString('PARTITION (`p1`)', $built->sql);
    }
    // endregion METHOD_testMySqlPartition

    // region METHOD_testPgSqlConflictTargetColumnsWithCollate [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): AdvancedTarget]
    /**
     * @purpose Verify PgSqlConflictTargetColumns renders COLLATE and opclass.
     */
    public function testPgSqlConflictTargetColumnsWithCollate(): void
    {
        $grammar = new PgSqlGrammar();

        $target = new PgSqlConflictTargetColumns([
            ['column' => 'name', 'collate' => 'C', 'opclass' => 'text_pattern_ops'],
        ]);

        $sql = $target->getSql($grammar);

        self::assertStringContainsString('"name" COLLATE "C" text_pattern_ops', $sql);
    }
    // endregion METHOD_testPgSqlConflictTargetColumnsWithCollate

    // region METHOD_testPgSqlConflictTargetColumnsExpr [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): AdvancedTarget]
    /**
     * @purpose Verify PgSqlConflictTargetColumns handles ExprInterface index expressions.
     */
    public function testPgSqlConflictTargetColumnsExpr(): void
    {
        $grammar = new PgSqlGrammar();

        $target = new PgSqlConflictTargetColumns([
            new Expr('lower(name)'),
        ]);

        $sql = $target->getSql($grammar);

        self::assertStringContainsString('(lower(name))', $sql);
    }
    // endregion METHOD_testPgSqlConflictTargetColumnsExpr

    // region METHOD_testPgSqlConflictTargetColumnsWhere [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): AdvancedTarget]
    /**
     * @purpose Verify PgSqlConflictTargetColumns renders WHERE predicate.
     */
    public function testPgSqlConflictTargetColumnsWhere(): void
    {
        $grammar = new PgSqlGrammar();

        $target = new PgSqlConflictTargetColumns(
            ['id'],
            ['users.active' => true],
        );

        $sql = $target->getSql($grammar);

        self::assertStringContainsString('WHERE', $sql);
    }
    // endregion METHOD_testPgSqlConflictTargetColumnsWhere

    // region METHOD_testPgSqlConflictActionDoUpdateScalarParams [DOMAIN(9): Testing; CONCEPT(9): Insert; TECH(9): Parameters]
    /**
     * @purpose Verify PgSqlConflictActionDoUpdate returns params from scalar SET values.
     */
    public function testPgSqlConflictActionDoUpdateScalarParams(): void
    {
        $grammar = new PgSqlGrammar();

        $action = new PgSqlConflictActionDoUpdate(
            ['name' => 'Bob'],
        );

        $action->getSql($grammar);
        $params = $action->getParams();

        self::assertNotEmpty($params);
    }
    // endregion METHOD_testPgSqlConflictActionDoUpdateScalarParams
}
// endregion CLASS_InsertQueryTest
