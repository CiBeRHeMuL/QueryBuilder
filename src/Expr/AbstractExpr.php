<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

// region MODULE_CONTRACT [DOMAIN(7): Expression; CONCEPT(7): Base; TECH(7): Abstract]
/**
 * @moduleContract
 * @purpose Base class for expression nodes that lazy-build their SQL on first access and cache the result.
 * @scope Lazy-building expressions, parameter caching.
 * @input GrammarInterface
 * @output string (SQL), array (params)
 * @invariants
 * - doBuild() is called at most once per grammar class.
 * - getParams() throws if getExpression() was not called first.
 * @modulemap
 * AbstractExpr => Base lazy-building expression class
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: AbstractExpr, lazy build, expression caching, base class
// STRUCTURE: ▶ getExpression ┌grammar┐ → ◇ cached in $expressions[grammar::class]? ⊕ return cached └─ else → ⚡ doBuild → ● cache [expr, params] → ∑ return expr

// region CLASS_AbstractExpr [DOMAIN(7): Expression; CONCEPT(7): Base; TECH(7): Abstract]
abstract class AbstractExpr implements ExprInterface
{
    protected array $params = [];
    /**
     * @var array<class-string<GrammarInterface>, string>
     */
    protected array $expressions = [];

    // region METHOD_getExpression [DOMAIN(7): Expression; CONCEPT(7): LazyBuild; TECH(7): Caching]
    /**
     * @purpose Lazy-build and cache the SQL expression string for a given grammar.
     * {@inheritDoc}
     */
    public function getExpression(GrammarInterface $grammar): string
    {
        if (!isset($this->expressions[$grammar::class])) {
            [$expr, $params] = $this->doBuild($grammar);
            $this->expressions[$grammar::class] = $expr;
            $this->params = $params;
        }

        return $this->expressions[$grammar::class];
    }
    // endregion METHOD_getExpression

    // region METHOD_getParams [DOMAIN(7): Expression; CONCEPT(6): Params; TECH(7): Accessor]
    /**
     * @purpose Return the bound parameters, ensuring the expression was built first.
     *
     * @throws QueryBuilderException
     *                               {@inheritDoc}
     */
    public function getParams(): array
    {
        if (!isset($this->params)) {
            throw QueryBuilderException::exprNotBuilt($this);
        }

        return $this->params;
    }
    // endregion METHOD_getParams

    // region METHOD_doBuild [DOMAIN(7): Expression; CONCEPT(7): Build; TECH(7): Abstract]
    /**
     * @purpose Builds the expression and returns an array of expression and params.
     *
     * @param GrammarInterface $grammar
     *
     * @return array
     */
    abstract protected function doBuild(GrammarInterface $grammar): array;
    // endregion METHOD_doBuild

    // region METHOD_generateParamId [DOMAIN(6): Expression; CONCEPT(5): Params; TECH(7): ID Generation]
    /**
     * @purpose Generates unique param id for expression without prefixed ":". Returned value will be like "v123_1"
     *
     * @return string
     */
    protected function generateParamId(): string
    {
        static $id = 0;

        $oid = spl_object_id($this);

        return sprintf('v%s_%s', $oid, ++$id);
    }
    // endregion METHOD_generateParamId
}
// endregion CLASS_AbstractExpr
