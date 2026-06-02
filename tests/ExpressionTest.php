<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Enum\Window\FrameBoundEnum;
use AndrewGos\QueryBuilder\Enum\Window\FrameExclusionEnum;
use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\DefaultValue;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\InExpr;
use AndrewGos\QueryBuilder\Expr\Literal;
use AndrewGos\QueryBuilder\Expr\OpExpr;
use AndrewGos\QueryBuilder\Expr\OrExpr;
use AndrewGos\QueryBuilder\Expr\Window\Over;
use AndrewGos\QueryBuilder\Expr\Window\Window;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use PHPUnit\Framework\TestCase;

// region CLASS_ExpressionTest [DOMAIN(9): Testing; CONCEPT(9): Expression; TECH(9): SQLGeneration]
/**
 * @purpose Test all expression nodes: Expr, Literal, DefaultValue, OpExpr, InExpr, AndExpr, OrExpr, Over, Window.
 */
class ExpressionTest extends TestCase
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

    // region METHOD_testExprConstruction [DOMAIN(9): Testing; CONCEPT(9): Expr; TECH(9): Immutable]
    /**
     * @purpose Verify Expr holds expression string and params immutably.
     */
    public function testExprConstruction(): void
    {
        $expr = new Expr('EXCLUDED.name', ['id' => 1]);
        self::assertSame('EXCLUDED.name', $expr->expression);
        self::assertSame(['id' => 1], $expr->params);
        self::assertSame('EXCLUDED.name', $expr->getExpression($this->grammar));
        self::assertSame(['id' => 1], $expr->getParams());
    }
    // endregion METHOD_testExprConstruction

    // region METHOD_testLiteralParamGeneration [DOMAIN(9): Testing; CONCEPT(9): Literal; TECH(9): ParamBinding]
    /**
     * @purpose Verify Literal generates unique param IDs and stores value.
     */
    public function testLiteralParamGeneration(): void
    {
        $literal = new Literal(42);
        $sql = $literal->getExpression($this->grammar);
        $params = $literal->getParams();

        self::assertStringStartsWith(':', $sql);
        self::assertCount(1, $params);
        self::assertContains(42, $params);
    }
    // endregion METHOD_testLiteralParamGeneration

    // region METHOD_testLiteralNullValue [DOMAIN(9): Testing; CONCEPT(9): Literal; TECH(9): Nullable]
    /**
     * @purpose Verify Literal can hold null value.
     */
    public function testLiteralNullValue(): void
    {
        $literal = new Literal(null);
        $sql = $literal->getExpression($this->grammar);
        $params = $literal->getParams();

        self::assertStringStartsWith(':', $sql);
        self::assertNull($params[array_key_first($params)]);
    }
    // endregion METHOD_testLiteralNullValue

    // region METHOD_testDefaultValue [DOMAIN(9): Testing; CONCEPT(9): DefaultValue; TECH(9): SQLKeyword]
    /**
     * @purpose Verify DefaultValue returns 'DEFAULT' and empty params.
     */
    public function testDefaultValue(): void
    {
        $dv = new DefaultValue();
        self::assertSame('DEFAULT', $dv->getExpression($this->grammar));
        self::assertEmpty($dv->getParams());
    }
    // endregion METHOD_testDefaultValue

    // region METHOD_testOpExprBasic [DOMAIN(9): Testing; CONCEPT(9): OpExpr; TECH(9): BinaryOp]
    /**
     * @purpose Verify OpExpr builds basic binary expression.
     */
    public function testOpExprBasic(): void
    {
        $op = new OpExpr(new Expr('"a"'), '=', new Expr('"b"'));
        $sql = $op->getExpression($this->grammar);
        self::assertSame('("a") = ("b")', $sql);
        self::assertSame([], $op->getParams());
    }
    // endregion METHOD_testOpExprBasic

    // region METHOD_testOpExprNullConversion [DOMAIN(9): Testing; CONCEPT(9): OpExpr; TECH(9): ISNULL]
    /**
     * @purpose Verify OpExpr converts `= NULL` to `IS NULL` in constructor.
     */
    public function testOpExprNullConversion(): void
    {
        $op = new OpExpr(new Expr('"a"'), '=', null);
        $sql = $op->getExpression($this->grammar);
        self::assertSame('("a") IS NULL', $sql);
        self::assertSame([], $op->getParams());
    }
    // endregion METHOD_testOpExprNullConversion

    // region METHOD_testOpExprBoolConversion [DOMAIN(9): Testing; CONCEPT(9): OpExpr; TECH(9): ISBOOL]
    /**
     * @purpose Verify OpExpr converts `= TRUE` to `IS TRUE` in constructor.
     */
    public function testOpExprBoolConversion(): void
    {
        $op = new OpExpr(new Expr('"a"'), '=', true);
        $sql = $op->getExpression($this->grammar);
        self::assertSame('("a") IS TRUE', $sql);
        self::assertSame([], $op->getParams());

        $op2 = new OpExpr(new Expr('"a"'), '=', false);
        $sql2 = $op2->getExpression($this->grammar);
        self::assertSame('("a") IS FALSE', $sql2);
        self::assertSame([], $op2->getParams());
    }
    // endregion METHOD_testOpExprBoolConversion

    // region METHOD_testInExprWithArray [DOMAIN(9): Testing; CONCEPT(9): InExpr; TECH(9): INList]
    /**
     * @purpose Verify InExpr builds IN list from array values.
     */
    public function testInExprWithArray(): void
    {
        $in = new InExpr(new Expr('"id"'), [1, 2, 3]);
        $sql = $in->getExpression($this->grammar);
        $params = $in->getParams();

        self::assertMatchesRegularExpression('/^\("id"\) IN \(:v\d+_\d+, :v\d+_\d+, :v\d+_\d+\)$/', $sql);
        self::assertCount(3, $params);
        self::assertContains(1, $params);
        self::assertContains(2, $params);
        self::assertContains(3, $params);
    }
    // endregion METHOD_testInExprWithArray

    // region METHOD_testAndExprSingle [DOMAIN(9): Testing; CONCEPT(9): AndExpr; TECH(9): SingleCondition]
    /**
     * @purpose Verify AndExpr with single condition renders without parentheses.
     */
    public function testAndExprSingle(): void
    {
        $and = new AndExpr([new Expr('"a" = 1')]);
        $sql = $and->getExpression($this->grammar);
        self::assertSame('"a" = 1', $sql);
    }
    // endregion METHOD_testAndExprSingle

    // region METHOD_testAndExprMultiple [DOMAIN(9): Testing; CONCEPT(9): AndExpr; TECH(9): MultipleConditions]
    /**
     * @purpose Verify AndExpr with multiple conditions wraps each in parentheses.
     */
    public function testAndExprMultiple(): void
    {
        $and = new AndExpr([new Expr('"a" = 1'), new Expr('"b" = 2')]);
        $sql = $and->getExpression($this->grammar);
        self::assertSame('("a" = 1) AND ("b" = 2)', $sql);
        self::assertSame([], $and->getParams());
    }
    // endregion METHOD_testAndExprMultiple

    // region METHOD_testOrExprSingle [DOMAIN(9): Testing; CONCEPT(9): OrExpr; TECH(9): SingleCondition]
    /**
     * @purpose Verify OrExpr with single condition renders without parentheses.
     */
    public function testOrExprSingle(): void
    {
        $or = new OrExpr([new Expr('"a" = 1')]);
        $sql = $or->getExpression($this->grammar);
        self::assertSame('"a" = 1', $sql);
    }
    // endregion METHOD_testOrExprSingle

    // region METHOD_testOrExprMultiple [DOMAIN(9): Testing; CONCEPT(9): OrExpr; TECH(9): MultipleConditions]
    /**
     * @purpose Verify OrExpr with multiple conditions wraps each in parentheses.
     */
    public function testOrExprMultiple(): void
    {
        $or = new OrExpr([new Expr('"a" = 1'), new Expr('"b" = 2')]);
        $sql = $or->getExpression($this->grammar);
        self::assertSame('("a" = 1) OR ("b" = 2)', $sql);
        self::assertSame([], $or->getParams());
    }
    // endregion METHOD_testOrExprMultiple

    // region METHOD_testWindowDefinition [DOMAIN(9): Testing; CONCEPT(9): Window; TECH(9): FluentAPI]
    /**
     * @purpose Verify Window fluent API builds full window definition.
     */
    public function testWindowDefinition(): void
    {
        $window = new Window();
        $window->partitionBy(['dept_id'])
            ->orderBy(['salary' => SORT_DESC])
            ->rows(FrameBoundEnum::Preceding, FrameBoundEnum::CurrentRow, 5);

        $sql = $window->getExpression($this->grammar);
        $params = $window->getParams();

        self::assertMatchesRegularExpression(
            '/^\(PARTITION BY "dept_id" ORDER BY "salary" DESC ROWS BETWEEN :v\d+_\d+ PRECEDING AND CURRENT ROW\)$/',
            $sql,
        );
        self::assertCount(1, $params);
        self::assertContains(5, $params);
    }
    // endregion METHOD_testWindowDefinition

    // region METHOD_testWindowExtend [DOMAIN(9): Testing; CONCEPT(9): Window; TECH(9): NamedWindow]
    /**
     * @purpose Verify Window can extend a named window.
     */
    public function testWindowExtend(): void
    {
        $window = new Window();
        $window->extend('w1');

        $sql = $window->getExpression($this->grammar);
        self::assertSame('("w1")', $sql);
    }
    // endregion METHOD_testWindowExtend

    // region METHOD_testWindowRange [DOMAIN(9): Testing; CONCEPT(9): Window; TECH(9): RangeFrame]
    /**
     * @purpose Verify Window range frame convenience method.
     */
    public function testWindowRange(): void
    {
        $window = new Window();
        $window->range(FrameBoundEnum::Preceding, FrameBoundEnum::CurrentRow);

        $sql = $window->getExpression($this->grammar);
        self::assertSame('(RANGE BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW)', $sql);
        self::assertSame([], $window->getParams());
    }
    // endregion METHOD_testWindowRange

    // region METHOD_testWindowGroups [DOMAIN(9): Testing; CONCEPT(9): Window; TECH(9): GroupsFrame)
    /**
     * @purpose Verify Window groups frame with exclusion.
     */
    public function testWindowGroups(): void
    {
        $window = new Window();
        $window->groups(
            FrameBoundEnum::Preceding,
            FrameBoundEnum::Following,
            3,
            2,
            FrameExclusionEnum::CurrentRow,
        );

        $sql = $window->getExpression($this->grammar);
        $params = $window->getParams();

        self::assertMatchesRegularExpression(
            '/^\(GROUPS BETWEEN :v\d+_\d+ PRECEDING AND :v\d+_\d+ FOLLOWING EXCLUDE CURRENT ROW\)$/',
            $sql,
        );
        self::assertCount(2, $params);
        self::assertContains(3, $params);
        self::assertContains(2, $params);
    }
    // endregion METHOD_testWindowGroups

    // region METHOD_testOverWithWindowName [DOMAIN(9): Testing; CONCEPT(9): Over; TECH(9): NamedWindow]
    /**
     * @purpose Verify Over clause with named window reference.
     */
    public function testOverWithWindowName(): void
    {
        $over = new Over(new Expr('row_number()'), 'w1');
        $sql = $over->getExpression($this->grammar);
        self::assertSame('row_number() OVER "w1"', $sql);
    }
    // endregion METHOD_testOverWithWindowName

    // region METHOD_testOverWithWindowDefinition [DOMAIN(9): Testing; CONCEPT(9): Over; TECH(9): InlineWindow]
    /**
     * @purpose Verify Over clause with inline window definition.
     */
    public function testOverWithWindowDefinition(): void
    {
        $window = new Window();
        $window->partitionBy(['dept_id']);
        $over = new Over(new Expr('row_number()'), $window);

        $sql = $over->getExpression($this->grammar);
        self::assertSame('row_number() OVER (PARTITION BY "dept_id")', $sql);
        self::assertSame([], $over->getParams());
    }
    // endregion METHOD_testOverWithWindowDefinition

    // region METHOD_testOpExprWithParams [DOMAIN(9): Testing; CONCEPT(9): OpExpr; TECH(9): ParamMerge]
    /**
     * @purpose Verify OpExpr merges params from both sides.
     */
    public function testOpExprWithParams(): void
    {
        $op = new OpExpr(new Literal(1), '=', new Literal(2));
        $sql = $op->getExpression($this->grammar);
        $params = $op->getParams();

        self::assertMatchesRegularExpression('/^\(:v\d+_\d+\) = \(:v\d+_\d+\)$/', $sql);
        self::assertCount(2, $params);
        self::assertContains(1, $params);
        self::assertContains(2, $params);
    }
    // endregion METHOD_testOpExprWithParams

    // region METHOD_testWindowAddPartitionBy [DOMAIN(9): Testing; CONCEPT(9): Window; TECH(9): AddPartitionBy]
    /**
     * @purpose Verify Window addPartitionBy appends to existing partitions.
     */
    public function testWindowAddPartitionBy(): void
    {
        $window = new Window();
        $window->partitionBy(['dept_id']);
        $window->addPartitionBy(['region']);

        $sql = $window->getExpression($this->grammar);
        self::assertSame('(PARTITION BY "dept_id", "region")', $sql);
        self::assertSame([], $window->getParams());
    }
    // endregion METHOD_testWindowAddPartitionBy

    // region METHOD_testWindowAddOrderBy [DOMAIN(9): Testing; CONCEPT(9): Window; TECH(9): AddOrderBy)
    /**
     * @purpose Verify Window addOrderBy appends to existing order.
     */
    public function testWindowAddOrderBy(): void
    {
        $window = new Window();
        $window->orderBy(['name' => SORT_ASC]);
        $window->addOrderBy(['age' => SORT_DESC]);

        $sql = $window->getExpression($this->grammar);
        self::assertSame('(ORDER BY "name" ASC, "age" DESC)', $sql);
        self::assertSame([], $window->getParams());
    }
    // endregion METHOD_testWindowAddOrderBy

    // region METHOD_testAbstractExprCaching [DOMAIN(9): Testing; CONCEPT(9): AbstractExpr; TECH(9): Caching]
    /**
     * @purpose Verify AbstractExpr caches expression per grammar class.
     */
    public function testAbstractExprCaching(): void
    {
        $literal = new Literal(42);
        $sql1 = $literal->getExpression($this->grammar);
        $sql2 = $literal->getExpression($this->grammar);
        self::assertSame($sql1, $sql2);
    }
    // endregion METHOD_testAbstractExprCaching

    // region METHOD_testInExprWithSubquery [DOMAIN(9): Testing; CONCEPT(9): InExpr; TECH(9): Subquery)
    /**
     * @purpose Verify InExpr with subquery (SelectQueryInterface).
     */
    public function testInExprWithSubquery(): void
    {
        $subQuery = new \AndrewGos\QueryBuilder\Query\Select\SelectQuery();
        $subQuery->select(['id'])->from(['users']);

        $in = new InExpr(new Expr('"id"'), $subQuery);
        $sql = $in->getExpression($this->grammar);

        self::assertSame('("id") IN (SELECT "id" FROM "users")', $sql);
        self::assertSame([], $in->getParams());
    }
    // endregion METHOD_testInExprWithSubquery
}
// endregion CLASS_ExpressionTest
