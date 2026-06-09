<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Tests;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Expr\AbstractExpr;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\Merge\MergeActionUpdate;
use AndrewGos\QueryBuilder\Grammar\AbstractGrammar;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;
use AndrewGos\QueryBuilder\Query\Values\ValuesQuery;
use PHPUnit\Framework\TestCase;

// region CLASS_ExprUnitTest [DOMAIN(9): Testing; CONCEPT(9): ExpressionUnit; TECH(9): UnitTest]
/**
 * @purpose Unit tests for expression internals: AbstractExpr param generation, MergeActionUpdate edge cases.
 */
class ExprUnitTest extends TestCase
{
    // region METHOD_testAbstractExprGenerateParamId [DOMAIN(9): Testing; CONCEPT(9): ExpressionUnit; TECH(9): ParamId]
    /**
     * @purpose Verify AbstractExpr::generateParamId produces unique IDs with correct format.
     */
    public function testAbstractExprGenerateParamId(): void
    {
        $expr = new class extends AbstractExpr {
            protected function doBuild(GrammarInterface $grammar): array
            {
                return ['', []];
            }
        };
        $ref = new \ReflectionMethod(AbstractExpr::class, 'generateParamId');
        $id1 = $ref->invoke($expr);
        $id2 = $ref->invoke($expr);

        // Justification: format is v{spl_object_id}_{counter} — both parts are dynamic
        self::assertMatchesRegularExpression('/^v\d+_\d+$/', $id1);
        self::assertMatchesRegularExpression('/^v\d+_\d+$/', $id2);
        self::assertNotSame($id1, $id2);
    }
    // endregion METHOD_testAbstractExprGenerateParamId

    // region METHOD_testExprNotBuiltExceptionMessage [DOMAIN(9): Testing; CONCEPT(9): ExpressionUnit; TECH(9): PreBuild]
    /**
     * @purpose Verify QueryBuilderException::exprNotBuilt message format.
     *         Note: AbstractExpr::getParams() has an isset($this->params) guard that never throws at runtime
     *         because $params is initialised as protected array $params = [], making isset() always true.
     *         Therefore the exception is only reachable via direct factory call, not through getParams().
     */
    public function testExprNotBuiltExceptionMessage(): void
    {
        $expr = new class extends AbstractExpr {
            protected function doBuild(GrammarInterface $grammar): array
            {
                return ['', []];
            }
        };
        $exception = QueryBuilderException::exprNotBuilt($expr);
        self::assertMatchesRegularExpression(
            '/Expression ".*" \(oid "\d+"\) is not built yet/',
            $exception->getMessage(),
        );
    }
    // endregion METHOD_testAbstractExprGetParamsBeforeBuild

    // region METHOD_testMergeActionUpdateGetParamsBeforeGetSql [DOMAIN(9): Testing; CONCEPT(9): ExpressionUnit; TECH(9): PreBuild]
    /**
     * @purpose Verify MergeActionUpdate::getParams returns empty array before getSql() is called (no exception, just empty).
     */
    public function testMergeActionUpdateGetParamsBeforeGetSql(): void
    {
        $action = new MergeActionUpdate(['col' => 'val']);
        self::assertSame([], $action->getParams());
    }
    // endregion METHOD_testMergeActionUpdateGetParamsBeforeGetSql

    // region METHOD_testMergeActionUpdateWithSelectQueryValue [DOMAIN(9): Testing; CONCEPT(9): ExpressionUnit; TECH(9): MergeUpdate]
    /**
     * @purpose Verify MergeActionUpdate with a SelectQuery as a SET value renders a subquery.
     */
    public function testMergeActionUpdateWithSelectQueryValue(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $sq = new SelectQuery()->select(['name'])->from(['source']);
        $action = new MergeActionUpdate(['col' => $sq]);
        $sql = $action->getSql($grammar);
        self::assertSame('UPDATE SET "col" = (SELECT "name" FROM "source")', $sql);
    }
    // endregion METHOD_testMergeActionUpdateWithSelectQueryValue

    // region METHOD_testMergeActionUpdateWithValuesQueryValue [DOMAIN(9): Testing; CONCEPT(9): ExpressionUnit; TECH(9): MergeUpdate]
    /**
     * @purpose Verify MergeActionUpdate with a ValuesQuery as a SET value renders a subquery.
     */
    public function testMergeActionUpdateWithValuesQueryValue(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $vq = new ValuesQuery()->values([[1]]);
        $action = new MergeActionUpdate(['col' => $vq]);
        $sql = $action->getSql($grammar);
        // Justification: VALUES converts int 1 to a named param placeholder
        self::assertMatchesRegularExpression(
            '/^UPDATE SET "col" = \(VALUES \(:v\d+_\d+\)\)$/',
            $sql,
        );
    }
    // endregion METHOD_testMergeActionUpdateWithValuesQueryValue

    // region METHOD_testMergeActionUpdateWithExprInterfaceValue [DOMAIN(9): Testing; CONCEPT(9): ExpressionUnit; TECH(9): MergeUpdate]
    /**
     * @purpose Verify MergeActionUpdate with an ExprInterface as a SET value renders the raw expression.
     */
    public function testMergeActionUpdateWithExprInterfaceValue(): void
    {
        $grammar = new class extends AbstractGrammar {
            public function escapeIdentifier(string $identifier): string
            {
                return '"' . $identifier . '"';
            }
        };
        $action = new MergeActionUpdate(['col' => new Expr('NOW()')]);
        $sql = $action->getSql($grammar);
        self::assertSame('UPDATE SET "col" = NOW()', $sql);
    }
    // endregion METHOD_testMergeActionUpdateWithExprInterfaceValue
}
// endregion CLASS_ExprUnitTest
