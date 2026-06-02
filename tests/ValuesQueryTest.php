<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Values\ValuesQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_ValuesQueryTest [DOMAIN(9): Testing; CONCEPT(9): ValuesQuery; TECH(9): SQLGeneration]
/**
 * @purpose Test VALUES queries including values(), addValues(), ORDER BY, LIMIT, and set operations.
 */
class ValuesQueryTest extends TestCase
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

    // region METHOD_testBasicValues [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): SingleRow]
    /**
     * @purpose Verify basic VALUES with single row.
     */
    public function testBasicValues(): void
    {
        $query = new ValuesQuery();
        $query->values([[1, 'Alice']]);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertStringContainsString('VALUES', $built->sql);
        self::assertCount(2, $built->params);
        self::assertContains(1, $built->params);
        self::assertContains('Alice', $built->params);
    }
    // endregion METHOD_testBasicValues

    // region METHOD_testMultipleRows [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): MultipleRows]
    /**
     * @purpose Verify VALUES with multiple rows.
     */
    public function testMultipleRows(): void
    {
        $query = new ValuesQuery();
        $query->values([[1, 'Alice'], [2, 'Bob']]);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertMatchesRegularExpression(
            '/^VALUES \(\(:v\d+_\d+, :v\d+_\d+\)\), \(\(:v\d+_\d+, :v\d+_\d+\)\)$/',
            $built->sql,
        );
        self::assertCount(4, $built->params);
    }
    // endregion METHOD_testMultipleRows

    // region METHOD_testAddValues [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): AddRows]
    /**
     * @purpose Verify addValues appends rows.
     */
    public function testAddValues(): void
    {
        $query = new ValuesQuery();
        $query->values([[1, 'Alice']]);
        $query->addValues([[2, 'Bob']]);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertStringContainsString('VALUES', $built->sql);
        self::assertCount(4, $built->params);
    }
    // endregion METHOD_testAddValues

    // region METHOD_testValuesWithOrderBy [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): OrderBy]
    /**
     * @purpose Verify VALUES with ORDER BY.
     */
    public function testValuesWithOrderBy(): void
    {
        $query = new ValuesQuery();
        $query->values([[1, 'Alice'], [2, 'Bob']]);
        $query->orderBy(['column1' => SORT_DESC]);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertStringContainsString('VALUES', $built->sql);
        self::assertStringContainsString('ORDER BY', $built->sql);
    }
    // endregion METHOD_testValuesWithOrderBy

    // region METHOD_testValuesWithLimit [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): Limit]
    /**
     * @purpose Verify VALUES with LIMIT.
     */
    public function testValuesWithLimit(): void
    {
        $query = new ValuesQuery();
        $query->values([[1, 'Alice'], [2, 'Bob']]);
        $query->limit(1);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertStringContainsString('VALUES', $built->sql);
        self::assertStringContainsString('FETCH FIRST 1 ROW ONLY', $built->sql);
    }
    // endregion METHOD_testValuesWithLimit

    // region METHOD_testValuesWithSetOperations [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): SetOperations]
    /**
     * @purpose Verify VALUES with UNION ALL set operation.
     */
    public function testValuesWithSetOperations(): void
    {
        $q2 = new SelectQuery();
        $q2->select(['3', 'Charlie']);

        $query = new ValuesQuery();
        $query->values([[1, 'Alice'], [2, 'Bob']]);
        $query->unionAll($q2);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertStringContainsString('VALUES', $built->sql);
        self::assertStringContainsString('UNION ALL', $built->sql);
    }
    // endregion METHOD_testValuesWithSetOperations

    // region METHOD_testValuesWithOffset [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): Offset]
    /**
     * @purpose Verify VALUES with OFFSET.
     */
    public function testValuesWithOffset(): void
    {
        $query = new ValuesQuery();
        $query->values([[1, 'Alice'], [2, 'Bob']]);
        $query->offset(1)->limit(1);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertStringContainsString('OFFSET 1', $built->sql);
        self::assertStringContainsString('FETCH NEXT 1 ROW ONLY', $built->sql);
    }
    // endregion METHOD_testValuesWithOffset

    // region METHOD_testValuesWithNullBool [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): NullBoolValues]
    /**
     * @purpose Verify VALUES row can contain null and bool values.
     */
    public function testValuesWithNullBool(): void
    {
        $query = new ValuesQuery();
        $query->values([[null, true, false]]);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertSame('VALUES ((NULL, TRUE, FALSE))', $built->sql);
        self::assertSame([], $built->params);
    }
    // endregion METHOD_testValuesWithNullBool

    // region METHOD_testValuesIsReturnable [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): Returnable]
    /**
     * @purpose Verify ValuesQuery always returns true from isReturnable.
     */
    public function testValuesIsReturnable(): void
    {
        $query = new ValuesQuery();
        self::assertTrue($query->isReturnable());
    }
    // endregion METHOD_testValuesIsReturnable

    // region METHOD_testValuesWithIntersect [DOMAIN(9): Testing; CONCEPT(9): Values; TECH(9): SetOps]
    /**
     * @purpose Verify VALUES with INTERSECT ALL.
     */
    public function testValuesWithIntersect(): void
    {
        $q2 = new SelectQuery();
        $q2->select(['1', 'Alice']);

        $query = new ValuesQuery();
        $query->values([[1, 'Alice'], [2, 'Bob']]);
        $query->intersectAll($q2);

        $built = $this->grammar->buildValuesQuery($query);
        self::assertStringContainsString('INTERSECT ALL', $built->sql);
    }
    // endregion METHOD_testValuesWithIntersect
}
// endregion CLASS_ValuesQueryTest
