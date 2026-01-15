<?php

namespace AndrewGos\QueryBuilder\Expr;

use AndrewGos\QueryBuilder\Exception\QueryBuilderException;
use AndrewGos\QueryBuilder\Grammar\GrammarInterface;

abstract class AbstractExpr implements ExprInterface
{
    protected array $params = [];
    /**
     * @var array<class-string<GrammarInterface>, string>
     */
    protected array $expressions = [];

    /**
     * @inheritDoc
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

    /**
     * @inheritDoc
     */
    public function getParams(): array
    {
        if (!isset($this->params)) {
            throw QueryBuilderException::exprNotBuilt($this);
        }
        return $this->params;
    }

    /**
     * Builds the expression and returns an array of expression and params.
     *
     * @param GrammarInterface $grammar
     *
     * @return array
     */
    abstract protected function doBuild(GrammarInterface $grammar): array;

    /**
     * Generates unique param id for expression without prefixed ":".
     * Returned value will be like "v123_1"
     *
     * @return string
     */
    protected function generateParamId(): string
    {
        static $id = 0;

        $oid = spl_object_id($this);
        return sprintf('v%s_%s', $oid, ++$id);
    }
}
