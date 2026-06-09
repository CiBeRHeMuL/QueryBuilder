<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Expression namespace — provides SQL expression nodes for the query builder.
 * @scope SQL fragments, boolean operations, comparisons, literals, default values.
 * @input Values, conditions, operators, grammars
 * @output SQL expression strings with bound parameters
 * @modulemap
 * ExprInterface [10][Base contract for all expression nodes] => ExprInterface.php
 * AbstractExpr [9][Lazy-building base class with caching] => AbstractExpr.php
 * Expr [8][Simple immutable expression node] => Expr.php
 * Literal [7][Parameterized literal value] => Literal.php
 * DefaultValue [6][SQL DEFAULT keyword] => DefaultValue.php
 * BoolOpsExpr [8][Abstract boolean operator base] => BoolOpsExpr.php
 * AndExpr [7][AND boolean expression] => AndExpr.php
 * OrExpr [7][OR boolean expression] => OrExpr.php
 * OpExpr [8][Binary operator expression] => OpExpr.php
 * InExpr [7][IN operator expression] => InExpr.php
 * @usecases
 * - ExprInterface: Grammar → Render expression → SQL
 * - BoolOpsExpr: Grammar → Build WHERE/HAVING → Joined conditions
 * - OpExpr: Grammar → Build comparison → Binary operator SQL
 */
