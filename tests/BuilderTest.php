<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Builder\ValueBuilder;
use AndrewGos\QueryBuilder\Enum\JoinTypeEnum;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Values\ValuesQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_BuilderTest [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): ValueDispatch]
/**
 * @purpose Test ValueBuilder dispatching of all value types (scalars, enums, expressions, subqueries, arrays).
 */
class BuilderTest extends TestCase
{
    private AbstractGrammar $grammar;
    private ValueBuilder $builder;

    protected function setUp(): void
    {
        $this->grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $this->builder = new ValueBuilder();
    }

    // region METHOD_testBuildNull [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): NullValue]
    /**
     * @purpose Verify null builds to 'NULL' expression.
     */
    public function testBuildNull(): void
    {
        $expr = $this->builder->build(null, $this->grammar);
        self::assertSame('NULL', $expr->getExpression($this->grammar));
        self::assertEmpty($expr->getParams());
    }
    // endregion METHOD_testBuildNull

    // region METHOD_testBuildBool [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): BoolValue]
    /**
     * @purpose Verify bool builds to 'TRUE' / 'FALSE'.
     */
    public function testBuildBool(): void
    {
        $trueExpr = $this->builder->build(true, $this->grammar);
        self::assertSame('TRUE', $trueExpr->getExpression($this->grammar));

        $falseExpr = $this->builder->build(false, $this->grammar);
        self::assertSame('FALSE', $falseExpr->getExpression($this->grammar));
    }
    // endregion METHOD_testBuildBool

    // region METHOD_testBuildScalar [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): ScalarValue]
    /**
     * @purpose Verify scalar values build to Literal with params.
     */
    public function testBuildScalar(): void
    {
        $intExpr = $this->builder->build(42, $this->grammar);
        self::assertMatchesRegularExpression('/^:v\d+_\d+$/', $intExpr->getExpression($this->grammar));
        self::assertSame([42], array_values($intExpr->getParams()));

        $floatExpr = $this->builder->build(3.14, $this->grammar);
        self::assertMatchesRegularExpression('/^:v\d+_\d+$/', $floatExpr->getExpression($this->grammar));
        self::assertSame([3.14], array_values($floatExpr->getParams()));

        $strExpr = $this->builder->build('hello', $this->grammar);
        self::assertMatchesRegularExpression('/^:v\d+_\d+$/', $strExpr->getExpression($this->grammar));
        self::assertSame(['hello'], array_values($strExpr->getParams()));
    }
    // endregion METHOD_testBuildScalar

    // region METHOD_testBuildStringAsIdentifier [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): StringAsId]
    /**
     * @purpose Verify string with $stringAsIdentifier=true builds to escaped identifier.
     */
    public function testBuildStringAsIdentifier(): void
    {
        $expr = $this->builder->build('users.id', $this->grammar, stringAsIdentifier: true);
        self::assertSame('"users"."id"', $expr->getExpression($this->grammar));
    }
    // endregion METHOD_testBuildStringAsIdentifier

    // region METHOD_testBuildExprInterface [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): Passthrough]
    /**
     * @purpose Verify ExprInterface value passes through as-is.
     */
    public function testBuildExprInterface(): void
    {
        $inner = new Expr('EXCLUDED.name');
        $expr = $this->builder->build($inner, $this->grammar);
        self::assertSame($inner, $expr);
    }
    // endregion METHOD_testBuildExprInterface

    // region METHOD_testBuildSubQuery [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): SubQuery]
    /**
     * @purpose Verify SelectQuery builds to parenthesized subquery.
     */
    public function testBuildSubQuery(): void
    {
        $query = new SelectQuery();
        $query->select(['id'])->from(['users']);

        $expr = $this->builder->build($query, $this->grammar);
        $sql = $expr->getExpression($this->grammar);

        self::assertSame('(SELECT "id" FROM "users")', $sql);
        self::assertSame([], $expr->getParams());
    }
    // endregion METHOD_testBuildSubQuery

    // region METHOD_testBuildValuesQuery [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): ValuesSubQuery)
    /**
     * @purpose Verify ValuesQuery builds to parenthesized subquery.
     */
    public function testBuildValuesQuery(): void
    {
        $query = new ValuesQuery();
        $query->values([[1, 'Alice']]);

        $expr = $this->builder->build($query, $this->grammar);
        $sql = $expr->getExpression($this->grammar);
        $params = $expr->getParams();

        self::assertMatchesRegularExpression('/^\(VALUES \(\(:v\d+_\d+, :v\d+_\d+\)\)\)$/', $sql);
        self::assertCount(2, $params);
        self::assertContains(1, $params);
        self::assertContains('Alice', $params);
    }
    // endregion METHOD_testBuildValuesQuery

    // region METHOD_testBuildEnum [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): EnumValue)
    /**
     * @purpose Verify UnitEnum builds to named parameter expression.
     */
    public function testBuildEnum(): void
    {
        $expr = $this->builder->build(JoinTypeEnum::InnerJoin, $this->grammar);
        $sql = $expr->getExpression($this->grammar);
        $params = $expr->getParams();

        self::assertMatchesRegularExpression('/^:v\d+_\d+$/', $sql);
        self::assertCount(1, $params);
        self::assertSame('InnerJoin', $params[array_key_first($params)]);
    }
    // endregion METHOD_testBuildEnum

    // region METHOD_testBuildArray [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): ArrayValue)
    /**
     * @purpose Verify array builds to parenthesized comma-separated list.
     */
    public function testBuildArray(): void
    {
        $expr = $this->builder->build([1, 2, 3], $this->grammar);
        $sql = $expr->getExpression($this->grammar);
        $params = $expr->getParams();

        self::assertMatchesRegularExpression('/^\(:v\d+_\d+, :v\d+_\d+, :v\d+_\d+\)$/', $sql);
        self::assertCount(3, $params);
        self::assertContains(1, $params);
        self::assertContains(2, $params);
        self::assertContains(3, $params);
    }
    // endregion METHOD_testBuildArray

    // region METHOD_testBuildEmptyArray [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): EmptyArray]
    /**
     * @purpose Verify empty array builds to empty parentheses.
     */
    public function testBuildEmptyArray(): void
    {
        $expr = $this->builder->build([], $this->grammar);
        self::assertSame('()', $expr->getExpression($this->grammar));
    }
    // endregion METHOD_testBuildEmptyArray

    // region METHOD_testBuildNestedArray [DOMAIN(9): Testing; CONCEPT(9): Builder; TECH(9): NestedArray)
    /**
     * @purpose Verify nested array values work.
     */
    public function testBuildNestedArray(): void
    {
        $expr = $this->builder->build([[1, 2], [3, 4]], $this->grammar);
        $sql = $expr->getExpression($this->grammar);
        $params = $expr->getParams();

        self::assertMatchesRegularExpression('/^\(\(:v\d+_\d+, :v\d+_\d+\), \(:v\d+_\d+, :v\d+_\d+\)\)$/', $sql);
        self::assertCount(4, $params);
        self::assertContains(1, $params);
        self::assertContains(2, $params);
        self::assertContains(3, $params);
        self::assertContains(4, $params);
    }
    // endregion METHOD_testBuildNestedArray
}
// endregion CLASS_BuilderTest
