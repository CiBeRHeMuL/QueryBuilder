<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\InExpr;
use AndrewGos\QueryBuilder\Expr\OpExpr;
use AndrewGos\QueryBuilder\Expr\Order\OrderColumn;
use AndrewGos\QueryBuilder\Expr\Table\SelectTable;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\BuiltQuery;
use AndrewGos\QueryBuilder\Helper\HExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Values\ValuesQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_HelperTest [DOMAIN(9): Testing; CONCEPT(9): Helper; TECH(9): Validation]
/**
 * @purpose Test HExpr validation methods (testExpr, testSelectExpr, etc.), normalization, and merge operations.
 */
class HelperTest extends TestCase
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

    // region METHOD_testExprValid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): ValidInputs]
    /**
     * @purpose Verify testExpr accepts valid types without throwing.
     */
    public function testExprValid(): void
    {
        HExpr::testExpr(null);
        HExpr::testExpr(true);
        HExpr::testExpr(42);
        HExpr::testExpr(3.14);
        HExpr::testExpr('string');
        HExpr::testExpr(new Expr('test'));
        HExpr::testExpr(new SelectQuery());
        HExpr::testExpr(new ValuesQuery());
        HExpr::testExpr([1, 2, 3]);

        self::assertTrue(true);
    }
    // endregion METHOD_testExprValid

    // region METHOD_testExprInvalid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): InvalidInputs]
    /**
     * @purpose Verify testExpr throws for invalid type.
     */
    public function testExprInvalid(): void
    {
        $this->expectException(QueryBuilderException::class);
        HExpr::testExpr(new \stdClass());
    }
    // endregion METHOD_testExprInvalid

    // region METHOD_testSelectExprValid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): ValidInputs]
    /**
     * @purpose Verify testSelectExpr accepts valid types.
     */
    public function testSelectExprValid(): void
    {
        HExpr::testSelectExpr(null);
        HExpr::testSelectExpr('col');
        HExpr::testSelectExpr(new Expr('COUNT(*)'));
        HExpr::testSelectExpr(new SelectQuery());

        self::assertTrue(true);
    }
    // endregion METHOD_testSelectExprValid

    // region METHOD_testSelectExprInvalid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): InvalidInputs]
    /**
     * @purpose Verify testSelectExpr throws for invalid type.
     */
    public function testSelectExprInvalid(): void
    {
        $this->expectException(QueryBuilderException::class);
        HExpr::testSelectExpr(new \stdClass());
    }
    // endregion METHOD_testSelectExprInvalid

    // region METHOD_testGroupByExprValid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): ValidInputs]
    /**
     * @purpose Verify testGroupByExpr accepts valid types.
     */
    public function testGroupByExprValid(): void
    {
        HExpr::testGroupByExpr(null);
        HExpr::testGroupByExpr('col');
        HExpr::testGroupByExpr(1);
        HExpr::testGroupByExpr(new Expr('col'));

        self::assertTrue(true);
    }
    // endregion METHOD_testGroupByExprValid

    // region METHOD_testGroupByExprInvalid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): InvalidInputs]
    /**
     * @purpose Verify testGroupByExpr throws for invalid type.
     */
    public function testGroupByExprInvalid(): void
    {
        $this->expectException(QueryBuilderException::class);
        HExpr::testGroupByExpr(new SelectQuery());
    }
    // endregion METHOD_testGroupByExprInvalid

    // region METHOD_testConditionValid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): ValidInputs]
    /**
     * @purpose Verify testCondition accepts valid types.
     */
    public function testConditionValid(): void
    {
        HExpr::testCondition(null);
        HExpr::testCondition(true);
        HExpr::testCondition('col = 1');
        HExpr::testCondition(new Expr('col = 1'));
        HExpr::testCondition(new SelectQuery());

        self::assertTrue(true);
    }
    // endregion METHOD_testConditionValid

    // region METHOD_testConditionInvalid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): InvalidInputs]
    /**
     * @purpose Verify testCondition throws for invalid type.
     */
    public function testConditionInvalid(): void
    {
        $this->expectException(QueryBuilderException::class);
        HExpr::testCondition(new \stdClass());
    }
    // endregion METHOD_testConditionInvalid

    // region METHOD_testStandaloneConditionValid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): ValidInputs]
    /**
     * @purpose Verify testStandaloneCondition accepts bool and ExprInterface.
     */
    public function testStandaloneConditionValid(): void
    {
        HExpr::testStandaloneCondition(true);
        HExpr::testStandaloneCondition(new Expr('test'));

        self::assertTrue(true);
    }
    // endregion METHOD_testStandaloneConditionValid

    // region METHOD_testStandaloneConditionInvalid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): InvalidInputs]
    /**
     * @purpose Verify testStandaloneCondition throws for non-bool/non-ExprInterface.
     */
    public function testStandaloneConditionInvalid(): void
    {
        $this->expectException(QueryBuilderException::class);
        HExpr::testStandaloneCondition('string');
    }
    // endregion METHOD_testStandaloneConditionInvalid

    // region METHOD_testTableValid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): ValidInputs]
    /**
     * @purpose Verify testTable accepts valid table types.
     */
    public function testTableValid(): void
    {
        HExpr::testTable('users');
        HExpr::testTable(new Expr('users'));
        HExpr::testTable(new SelectQuery());
        HExpr::testTable(new ValuesQuery());
        HExpr::testTable(new SelectTable('users'));

        self::assertTrue(true);
    }
    // endregion METHOD_testTableValid

    // region METHOD_testTableInvalid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): InvalidInputs]
    /**
     * @purpose Verify testTable throws for invalid type.
     */
    public function testTableInvalid(): void
    {
        $this->expectException(QueryBuilderException::class);
        HExpr::testTable(42);
    }
    // endregion METHOD_testTableInvalid

    // region METHOD_testNormalizeConditionsStringKey [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): ShortSyntax]
    /**
     * @purpose Verify normalizeConditions converts string-keyed scalars to OpExpr.
     */
    public function testNormalizeConditionsStringKey(): void
    {
        $conditions = ['name' => 'Alice', 'age' => 25];
        $result = HExpr::normalizeConditions($conditions, $this->grammar);

        self::assertCount(2, $result);
        foreach ($result as $expr) {
            self::assertInstanceOf(OpExpr::class, $expr);
        }
        $sql1 = $result[0]->getExpression($this->grammar);
        $sql2 = $result[1]->getExpression($this->grammar);
        self::assertMatchesRegularExpression('/^"name" = :v\d+_\d+$/', $sql1);
        self::assertMatchesRegularExpression('/^"age" = :v\d+_\d+$/', $sql2);
    }
    // endregion METHOD_testNormalizeConditionsStringKey

    // region METHOD_testNormalizeConditionsArrayToInExpr [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): InExpr)
    /**
     * @purpose Verify normalizeConditions converts string-keyed arrays to InExpr.
     */
    public function testNormalizeConditionsArrayToInExpr(): void
    {
        $conditions = ['id' => [1, 2, 3]];
        $result = HExpr::normalizeConditions($conditions, $this->grammar);

        self::assertCount(1, $result);
        self::assertInstanceOf(InExpr::class, $result[0]);
        $sql = $result[0]->getExpression($this->grammar);
        self::assertMatchesRegularExpression('/^"id" IN \(:v\d+_\d+, :v\d+_\d+, :v\d+_\d+\)$/', $sql);
        $params = $result[0]->getParams();
        self::assertCount(3, $params);
        self::assertContains(1, $params);
        self::assertContains(2, $params);
        self::assertContains(3, $params);
    }
    // endregion METHOD_testNormalizeConditionsArrayToInExpr

    // region METHOD_testNormalizeConditionsIntKey [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): Standalone]
    /**
     * @purpose Verify normalizeConditions passes through int-keyed ExprInterface conditions.
     */
    public function testNormalizeConditionsIntKey(): void
    {
        $conditions = [new Expr('"a" = 1')];
        $result = HExpr::normalizeConditions($conditions, $this->grammar);

        self::assertCount(1, $result);
        self::assertSame($conditions[0], $result[0]);
    }
    // endregion METHOD_testNormalizeConditionsIntKey

    // region METHOD_testNormalizeConditionsBool [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): BoolCondition)
    /**
     * @purpose Verify normalizeConditions converts int-keyed bool to ExprInterface via ValueBuilder.
     */
    public function testNormalizeConditionsBool(): void
    {
        $conditions = [true, false];
        $result = HExpr::normalizeConditions($conditions, $this->grammar);

        self::assertCount(2, $result);
        self::assertSame('TRUE', $result[0]->getExpression($this->grammar));
        self::assertSame([], $result[0]->getParams());
        self::assertSame('FALSE', $result[1]->getExpression($this->grammar));
        self::assertSame([], $result[1]->getParams());
    }
    // endregion METHOD_testNormalizeConditionsBool

    // region METHOD_testNormalizeOrderByStringKey [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): ShortSyntax]
    /**
     * @purpose Verify normalizeOrderBy converts string keys with SORT_ASC/SORT_DESC to OrderColumn.
     */
    public function testNormalizeOrderByStringKey(): void
    {
        $result = HExpr::normalizeOrderBy(['name' => SORT_ASC, 'age' => SORT_DESC]);

        self::assertCount(2, $result);
        self::assertInstanceOf(OrderColumn::class, $result[0]);
        self::assertSame('ASC', $result[0]->order);
        self::assertSame('DESC', $result[1]->order);
    }
    // endregion METHOD_testNormalizeOrderByStringKey

    // region METHOD_testNormalizeOrderByIntKey [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): Expression]
    /**
     * @purpose Verify normalizeOrderBy converts int-keyed strings to OrderColumn with default ASC.
     */
    public function testNormalizeOrderByIntKey(): void
    {
        $result = HExpr::normalizeOrderBy(['name']);

        self::assertCount(1, $result);
        self::assertInstanceOf(OrderColumn::class, $result[0]);
        self::assertSame('name', $result[0]->expr);
        self::assertSame('ASC', $result[0]->order);
    }
    // endregion METHOD_testNormalizeOrderByIntKey

    // region METHOD_testNormalizeOrderByOrderColumn [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): OrderColumnPassthrough]
    /**
     * @purpose Verify normalizeOrderBy passes through OrderColumn objects.
     */
    public function testNormalizeOrderByOrderColumn(): void
    {
        $col = new OrderColumn('name', 'DESC');
        $result = HExpr::normalizeOrderBy([$col]);

        self::assertCount(1, $result);
        self::assertSame($col, $result[0]);
    }
    // endregion METHOD_testNormalizeOrderByOrderColumn

    // region METHOD_testNormalizeOrderByStringDirection [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): StringDirection]
    /**
     * @purpose Verify normalizeOrderBy handles string direction.
     */
    public function testNormalizeOrderByStringDirection(): void
    {
        $result = HExpr::normalizeOrderBy(['name' => 'ASC NULLS LAST']);
        self::assertCount(1, $result);
        self::assertSame('ASC NULLS LAST', $result[0]->order);
    }
    // endregion METHOD_testNormalizeOrderByStringDirection

    // region METHOD_testNormalizeConditionsWithSelectQuery [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): SubqueryCondition]
    /**
     * @purpose Verify normalizeConditions converts string-keyed SelectQuery to InExpr.
     */
    public function testNormalizeConditionsWithSelectQuery(): void
    {
        $subQuery = new SelectQuery();
        $subQuery->select(['id'])->from(['users']);

        $conditions = ['id' => $subQuery];
        $result = HExpr::normalizeConditions($conditions, $this->grammar);

        self::assertCount(1, $result);
        self::assertInstanceOf(InExpr::class, $result[0]);
        $sql = $result[0]->getExpression($this->grammar);
        self::assertSame('"id" IN (SELECT "id" FROM "users")', $sql);
        self::assertSame([], $result[0]->getParams());
    }
    // endregion METHOD_testNormalizeConditionsWithSelectQuery

    // region METHOD_testConditionsArrayInvalid [DOMAIN(9): Testing; CONCEPT(9): Validation; TECH(9): InvalidArray]
    /**
     * @purpose Verify testConditionsArray throws for invalid entries.
     */
    public function testConditionsArrayInvalid(): void
    {
        $this->expectException(QueryBuilderException::class);
        HExpr::testConditionsArray([new \stdClass()]);
    }
    // endregion METHOD_testConditionsArrayInvalid

    // region METHOD_testNormalizeOrderByExprInterface [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): ExprValue]
    /**
     * @purpose Verify normalizeOrderBy wraps ExprInterface in OrderColumn with ASC.
     */
    public function testNormalizeOrderByExprInterface(): void
    {
        $expr = new Expr('LOWER(name)');
        $result = HExpr::normalizeOrderBy([$expr]);

        self::assertCount(1, $result);
        self::assertInstanceOf(OrderColumn::class, $result[0]);
        self::assertSame($expr, $result[0]->expr);
        self::assertSame('ASC', $result[0]->order);
    }
    // endregion METHOD_testNormalizeOrderByExprInterface

    // region METHOD_testNormalizeTableString [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): Table]
    /**
     * @purpose Verify normalizeTable converts string to SelectTable.
     */
    public function testNormalizeTableString(): void
    {
        $result = HExpr::normalizeTable('users');
        self::assertInstanceOf(SelectTable::class, $result);
        self::assertSame('users', $result->name);
    }
    // endregion METHOD_testNormalizeTableString

    // region METHOD_testNormalizeTablePassthrough [DOMAIN(9): Testing; CONCEPT(9): Normalization; TECH(9): Passthrough]
    /**
     * @purpose Verify normalizeTable passes through non-string values.
     */
    public function testNormalizeTablePassthrough(): void
    {
        $table = new SelectTable('users');
        $result = HExpr::normalizeTable($table);
        self::assertSame($table, $result);
    }
    // endregion METHOD_testNormalizeTablePassthrough

    // region METHOD_testMergeParams [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): Params]
    /**
     * @purpose Verify mergeParams handles numeric and string keys.
     */
    public function testMergeParams(): void
    {
        $result = HExpr::mergeParams([':a' => 1], [':b' => 2], [3, 4]);
        self::assertSame([':a' => 1, ':b' => 2, 0 => 3, 1 => 4], $result);
    }
    // endregion METHOD_testMergeParams

    // region METHOD_testMergeParamsStringKeyOverride [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): Override]
    /**
     * @purpose Verify mergeParams string keys override earlier values.
     */
    public function testMergeParamsStringKeyOverride(): void
    {
        $result = HExpr::mergeParams([':a' => 1], [':a' => 2]);
        self::assertSame([':a' => 2], $result);
    }
    // endregion METHOD_testMergeParamsStringKeyOverride

    // region METHOD_testMergeExpressionParts [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): Parts]
    /**
     * @purpose Verify mergeExpressionParts merges ExprInterface, BuiltQuery, and string.
     */
    public function testMergeExpressionParts(): void
    {
        $result = HExpr::mergeExpressionParts(
            [new Expr('SELECT'), new BuiltQuery('1', []), 'FROM'],
            $this->grammar,
            ' ',
        );

        self::assertSame('SELECT 1 FROM', $result->getExpression($this->grammar));
    }
    // endregion METHOD_testMergeExpressionParts

    // region METHOD_testMergeExpressionPartsWithParams [DOMAIN(9): Testing; CONCEPT(9): Merge; TECH(9): Params]
    /**
     * @purpose Verify mergeExpressionParts merges params from ExprInterface and BuiltQuery.
     */
    public function testMergeExpressionPartsWithParams(): void
    {
        $result = HExpr::mergeExpressionParts(
            [new Expr(':a', [':a' => 1]), new BuiltQuery(':b', [':b' => 2])],
            $this->grammar,
            ', ',
        );

        self::assertSame([':a' => 1, ':b' => 2], $result->getParams());
    }
    // endregion METHOD_testMergeExpressionPartsWithParams
}
// endregion CLASS_HelperTest
