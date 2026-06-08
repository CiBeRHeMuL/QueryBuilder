<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\MySql\MySqlSelectQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Update\MySql\MySqlUpdateQuery;
use AndrewGos\QueryBuilder\Query\Update\PgSql\PgSqlUpdateQuery;
use AndrewGos\QueryBuilder\Query\Update\UpdateQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_BugFixTest [DOMAIN(9): Testing; CONCEPT(9): Regression; TECH(9): BugFixes]
/**
 * @purpose Regression tests for code audit bug fixes #1-#7.
 */
class BugFixTest extends TestCase
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

    // region METHOD_testBug1MySqlLimitExprInterface [DOMAIN(9): Testing; CONCEPT(9): BugFix; TECH(9): MySql]
    /**
     * @purpose Bug #1: MySqlGrammar::buildLimitClause handles ExprInterface for offset and limit.
     */
    public function testBug1MySqlLimitExprInterface(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlSelectQuery();
        $query->select(['id'])->from(['users']);
        $query->offset(new Expr(':custom_offset', ['custom_offset' => 5]))
              ->limit(new Expr(':custom_limit', ['custom_limit' => 10]));

        $built = $grammar->buildSelectQuery($query);
        self::assertStringContainsString('LIMIT :custom_offset, :custom_limit', $built->sql);
        self::assertArrayHasKey('custom_offset', $built->params);
        self::assertArrayHasKey('custom_limit', $built->params);
    }

    /**
     * @purpose Bug #1: MySqlGrammar::buildLimitClause with mixed int offset and ExprInterface limit.
     */
    public function testBug1MySqlLimitMixedIntAndExpr(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlSelectQuery();
        $query->select(['id'])->from(['users']);
        $query->offset(5)->limit(new Expr(':custom_limit', ['custom_limit' => 10]));

        $built = $grammar->buildSelectQuery($query);
        self::assertMatchesRegularExpression(
            '/^SELECT `id` FROM `users` LIMIT :v\d+_\d+, :custom_limit$/',
            $built->sql,
        );
        self::assertArrayHasKey('custom_limit', $built->params);
    }
    // endregion METHOD_testBug1MySqlLimitExprInterface

    // region METHOD_testBug2AndWhereStringKeys [DOMAIN(9): Testing; CONCEPT(9): BugFix; TECH(9): WhereTrait]
    /**
     * @purpose Bug #2: andWhere does not overwrite existing conditions with same string keys.
     */
    public function testBug2AndWhereStringKeys(): void
    {
        $grammar = $this->grammar;

        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);
        $query->where(['id' => 1]);
        $query->andWhere(['id' => 2]);

        $built = $grammar->buildSelectQuery($query);
        // Both conditions should be present (two = operators, not one)
        self::assertSame(2, substr_count($built->sql, '= '));
    }

    /**
     * @purpose Bug #2: andHaving does not overwrite existing HAVING conditions with same keys.
     */
    public function testBug2AndHavingStringKeys(): void
    {
        $grammar = $this->grammar;

        $query = new SelectQuery();
        $query->select(['id'])->from(['users'])->groupBy(['id']);
        $query->having([new Expr('COUNT(*) > 1')]);
        $query->andHaving([new Expr('COUNT(*) > 0')]);

        $built = $grammar->buildSelectQuery($query);
        // Both conditions should be present
        self::assertStringContainsString('COUNT(*) > 1', $built->sql);
        self::assertStringContainsString('COUNT(*) > 0', $built->sql);
    }
    // endregion METHOD_testBug2AndWhereStringKeys

    // region METHOD_testBug3AddWithWarning [DOMAIN(9): Testing; CONCEPT(9): BugFix; TECH(9): WithTrait]
    /**
     * @purpose Bug #3: Verify addWith overwrites CTE with same alias (documented behavior).
     */
    public function testBug3AddWithOverwrite(): void
    {
        $grammar = $this->grammar;

        $inner1 = new SelectQuery();
        $inner1->select(['id'])->from(['users']);

        $inner2 = new SelectQuery();
        $inner2->select(['name'])->from(['users']);

        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);
        $query->with(['cte' => new WithQuery($inner1)]);
        $query->addWith(['cte' => new WithQuery($inner2)]);

        $built = $grammar->buildSelectQuery($query);
        // CTE 'cte' should contain the second definition (overwritten)
        self::assertStringContainsString('"name"', $built->sql);
        self::assertStringNotContainsString('"id" AS "name"', $built->sql);
    }
    // endregion METHOD_testBug3AddWithWarning

    // region METHOD_testBug4BoolOpsExprEmpty [DOMAIN(9): Testing; CONCEPT(9): BugFix; TECH(9): BoolOpsExpr]
    /**
     * @purpose Bug #4: BoolOpsExpr throws on empty conditions array instead of crashing.
     */
    public function testBug4AndExprEmptyThrows(): void
    {
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('AND/OR expression requires at least one condition');

        $expr = new AndExpr([]);
        $expr->getExpression($this->grammar);
    }

    /**
     * @purpose Bug #4: orHaving on empty having triggers BoolOpsExpr crash path.
     */
    public function testBug4OrHavingWithoutHavingThrows(): void
    {
        $grammar = $this->grammar;

        $query = new SelectQuery();
        $query->select(['id'])->from(['users'])->groupBy(['id']);
        $query->orHaving([]);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('AND/OR expression requires at least one condition');
        $grammar->buildSelectQuery($query);
    }

    /**
     * @purpose Bug #4: orWhere without prior where triggers the same crash path.
     */
    public function testBug4OrWhereWithoutWhereThrows(): void
    {
        $grammar = $this->grammar;

        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);
        $query->orWhere([]);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('AND/OR expression requires at least one condition');
        $grammar->buildSelectQuery($query);
    }
    // endregion METHOD_testBug4BoolOpsExprEmpty

    // region METHOD_testBug5TestSelectExprDelegates [DOMAIN(9): Testing; CONCEPT(9): BugFix; TECH(9): HExpr]
    /**
     * @purpose Bug #5: testSelectExpr delegates to testExpr — both accept/reject same types.
     */
    public function testBug5TestSelectExprValidInput(): void
    {
        // Must not throw
        HExpr::testSelectExpr(null);
        HExpr::testSelectExpr(true);
        HExpr::testSelectExpr(42);
        HExpr::testSelectExpr(3.14);
        HExpr::testSelectExpr('column');
        HExpr::testSelectExpr(new Expr('1'));
        HExpr::testSelectExpr([]);

        $this->expectException(QueryBuilderException::class);
        HExpr::testSelectExpr(new \stdClass());
    }
    // endregion METHOD_testBug5TestSelectExprDelegates

    // region METHOD_testBug6OpExprParamsInit [DOMAIN(9): Testing; CONCEPT(9): BugFix; TECH(9): OpExpr]
    /**
     * @purpose Bug #6: OpExpr::doBuild correctly initializes $params.
     */
    public function testBug6OpExprReturnsParams(): void
    {
        $expr = new \AndrewGos\QueryBuilder\Expr\OpExpr(
            new Expr(':left'),
            '=',
            new Expr(':right'),
        );

        $result = $expr->getExpression($this->grammar);
        // Must not throw and must return params
        self::assertIsString($result);
        // We can't check params directly, but building should work
        self::assertNotNull($expr->getParams());
    }
    // endregion METHOD_testBug6OpExprParamsInit

    // region METHOD_testBug7ValidateUpdateQuery [DOMAIN(9): Testing; CONCEPT(9): BugFix; TECH(9): Validation]
    /**
     * @purpose Bug #7: AbstractGrammar buildUpdateQuery throws when table is empty.
     */
    public function testBug7AbstractGrammarEmptyTableThrows(): void
    {
        $query = new UpdateQuery();
        $query->set(['name' => 'Alice']);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('UPDATE query requires a table name');
        $this->grammar->buildUpdateQuery($query);
    }

    /**
     * @purpose Bug #7: AbstractGrammar buildUpdateQuery throws when no SET clauses.
     */
    public function testBug7AbstractGrammarNoSetThrows(): void
    {
        $query = new UpdateQuery();
        $query->table('users');

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('UPDATE query requires at least one SET clause');
        $this->grammar->buildUpdateQuery($query);
    }

    /**
     * @purpose Bug #7: PgSqlGrammar buildUpdateQuery throws when table is empty.
     */
    public function testBug7PgSqlGrammarEmptyTableThrows(): void
    {
        $grammar = new PgSqlGrammar();

        $query = new PgSqlUpdateQuery();
        $query->set(['name' => 'Alice']);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('UPDATE query requires a table name');
        $grammar->buildUpdateQuery($query);
    }

    /**
     * @purpose Bug #7: MySqlGrammar buildUpdateQuery throws when table is empty.
     */
    public function testBug7MySqlGrammarEmptyTableThrows(): void
    {
        $grammar = new MySqlGrammar();

        $query = new MySqlUpdateQuery();
        $query->set(['name' => 'Alice']);

        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('UPDATE query requires a table name');
        $grammar->buildUpdateQuery($query);
    }
    // endregion METHOD_testBug7ValidateUpdateQuery
}
// endregion CLASS_BugFixTest
