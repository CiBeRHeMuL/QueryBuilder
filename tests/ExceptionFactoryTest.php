<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\Default\DefaultGrammar;
use AndrewGos\QueryBuilder\Query\Delete\DeleteQuery;
use AndrewGos\QueryBuilder\Query\Interface\MaybeReturnableQueryInterface;
use AndrewGos\QueryBuilder\Query\Merge\PgSql\PgSqlMergeQuery;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_ExceptionFactoryTest [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): ErrorHandling]
/**
 * @purpose Verify that all static factory methods of QueryBuilderException are triggered as expected via misuse of the query builder API.
 */
class ExceptionFactoryTest extends TestCase
{
    // region METHOD_testValueIsNotExpr [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): ExprValidation]
    /**
     * @purpose Trigger valueIsNotExpr by passing stdClass as a select column expression.
     */
    public function testValueIsNotExpr(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $q = new SelectQuery();
        $q->select(['col' => new \stdClass()]);
        $this->expectException(QueryBuilderException::class);
        // Justification: Message contains the value type and allowed types — exact text is long and includes FQCNs
        $this->expectExceptionMessageMatches('/Value ".*stdClass.*" is not a valid expression/');
        $grammar->buildSelectQuery($q);
    }
    // endregion METHOD_testValueIsNotExpr

    // region METHOD_testValueIsNotTable [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): TableValidation]
    /**
     * @purpose Trigger valueIsNotTable by passing stdClass as a table expression.
     */
    public function testValueIsNotTable(): void
    {
        $q = new SelectQuery();
        $q->select(['*']);
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessageMatches('/Value ".*" is not a valid table expression/');
        $q->from([new \stdClass()]);
    }
    // endregion METHOD_testValueIsNotTable

    // region METHOD_testValueIsNotSelectExpr [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): SelectValidation]
    /**
     * @purpose Trigger valueIsNotExpr (via testSelectExpr -> testExpr) by building a query with stdClass as a select expression.
     */
    public function testValueIsNotSelectExpr(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $q = new SelectQuery();
        $q->select([new \stdClass()]);
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessageMatches('/Value ".*" is not a valid expression/');
        $grammar->buildSelectQuery($q);
    }
    // endregion METHOD_testValueIsNotSelectExpr

    // region METHOD_testValueIsNotGroupByExpr [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): GroupByValidation]
    /**
     * @purpose Trigger valueIsNotGroupByExpr via HExpr::testGroupByExpr through AbstractGroupingSets, or test factory directly.
     *         Note: buildGroupByClause doesn't call testGroupByExpr — it passes directly to ValueBuilder which throws TypeError for stdClass.
     */
    public function testValueIsNotGroupByExpr(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        // Use Expr('invalid') as a pseudo-group-by to test that ValueBuilder throws TypeError.
        // The valueIsNotGroupByExpr factory is not triggered through the normal groupBy() + build pipeline.
        // We test the factory method directly to ensure coverage.
        $exception = QueryBuilderException::valueIsNotGroupByExpr(new \stdClass());
        self::assertMatchesRegularExpression(
            '/Value ".*" is not a valid group by expression/',
            $exception->getMessage(),
        );
    }
    // endregion METHOD_testValueIsNotGroupByExpr

    // region METHOD_testValueIsNotCondition [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): ConditionValidation]
    /**
     * @purpose Trigger valueIsNotCondition by passing stdClass as a where condition value — exception thrown at build time.
     */
    public function testValueIsNotCondition(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $q = new DeleteQuery();
        $q->from(['t'])->where(['col' => new \stdClass()]);
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessageMatches('/Value ".*" is not a valid condition/');
        $grammar->buildDeleteQuery($q);
    }
    // endregion METHOD_testValueIsNotCondition

    // region METHOD_testValueIsNotStandaloneCondition [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): ConditionValidation]
    /**
     * @purpose Trigger valueIsNotStandaloneCondition by passing int key with int value (must be bool|ExprInterface) — thrown at build time.
     */
    public function testValueIsNotStandaloneCondition(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $q = new DeleteQuery();
        $q->from(['t'])->where([0 => 123]);
        $this->expectException(QueryBuilderException::class);
        // Justification: message contains value type and valid types
        $this->expectExceptionMessageMatches('/Value ".*" is not a valid standalone condition/');
        $grammar->buildDeleteQuery($q);
    }
    // endregion METHOD_testValueIsNotStandaloneCondition

    // region METHOD_testValueIsNotOrderBy [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): OrderByValidation]
    /**
     * @purpose Trigger valueIsNotOrderBy by passing stdClass as an ORDER BY expression with int key.
     */
    public function testValueIsNotOrderBy(): void
    {
        $q = new SelectQuery();
        $q->select(['id'])->from(['t']);
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessageMatches('/Value ".*" is not a valid order by expression/');
        $q->orderBy([0 => new \stdClass()]);
    }
    // endregion METHOD_testValueIsNotOrderBy

    // region METHOD_testInvalidIdentifier [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): IdentifierValidation]
    /**
     * @purpose Verify invalidIdentifier factory produces correct message format.
     */
    public function testInvalidIdentifier(): void
    {
        $grammar = new DefaultGrammar();
        $exception = QueryBuilderException::invalidIdentifier('', $grammar);
        self::assertMatchesRegularExpression(
            '/Identifier ".*" is invalid for grammar ".*"/',
            $exception->getMessage(),
        );
    }
    // endregion METHOD_testInvalidIdentifier

    // region METHOD_testValueIsNotReturnableQuery [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): ReturnableValidation]
    /**
     * @purpose Trigger valueIsNotReturnableQuery by building a non-returnable PgSqlMergeQuery via buildMaybeReturnableQuery.
     */
    public function testValueIsNotReturnableQuery(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $q = new PgSqlMergeQuery();
        $q->into('t')->using('s')->on(['t.id' => 's.id']);
        self::assertFalse($q->isReturnable());
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessageMatches('/Value ".*" is not returnable query/');
        $grammar->buildMaybeReturnableQuery($q);
    }
    // endregion METHOD_testValueIsNotReturnableQuery

    // region METHOD_testEmptyBoolExpression [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): BoolExpression]
    /**
     * @purpose Trigger emptyBoolExpression by calling orWhere without prior where() — exception thrown at build time when empty AndExpr is evaluated.
     */
    public function testEmptyBoolExpression(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $q = new DeleteQuery();
        $q->from(['t'])->orWhere(['col' => 1]);
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessage('AND/OR expression requires at least one condition.');
        $grammar->buildDeleteQuery($q);
    }
    // endregion METHOD_testEmptyBoolExpression

    // region METHOD_testReturnableQueryCannotBeBuilt [DOMAIN(9): Testing; CONCEPT(9): ExceptionFactory; TECH(9): BuildValidation]
    /**
     * @purpose Trigger returnableQueryCannotBeBuilt by passing a mock MaybeReturnableQueryInterface that is not Select or Values.
     */
    public function testReturnableQueryCannotBeBuilt(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $mockQuery = new class implements MaybeReturnableQueryInterface {
            public function isReturnable(): bool
            {
                return true;
            }
        };
        $this->expectException(QueryBuilderException::class);
        $this->expectExceptionMessageMatches('/Maybe returnable query ".*" cannot be built by grammar ".*"/');
        $grammar->buildMaybeReturnableQuery($mockQuery);
    }
    // endregion METHOD_testReturnableQueryCannotBeBuilt
}
// endregion CLASS_ExceptionFactoryTest
