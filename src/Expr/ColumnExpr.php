<?php

declare(strict_types=1);

namespace AndrewGos\QueryBuilder\Expr;

// region MODULE_CONTRACT [DOMAIN(7): Expression; CONCEPT(7): Column; TECH(6): Marker]
/**
 * @moduleContract
 * @purpose Marker class for simple column identifiers. Extends Expr with no additional logic — used to signal
 *          to OpExpr::doBuild that this expression is a plain column identifier and should NOT be parenthesized.
 * @scope Column identifier expressions generated via short syntax (['column' => value]).
 * @input Escaped identifier string
 * @output Same as Expr — SQL fragment with params
 * @rationale
 * Q: Why a separate class instead of a flag?
 * A: A dedicated type is semantically explicit, requires no BC breaks in OpExpr constructor, and prevents
 *    parenthesization of simple column identifiers while keeping full Expr behavior for other cases.
 * @changes
 * LAST_CHANGE: [v1.0.0 – Initial creation for column identifier marker.]
 * @modulemap
 * CLASS 10[Column identifier marker] => ColumnExpr
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: ColumnExpr, column identifier, marker, expression

// region CLASS_ColumnExpr [DOMAIN(7): Expression; CONCEPT(7): Column; TECH(6): Marker]
/**
 * @purpose Marker class: a simple column identifier. Subclasses Expr without adding behavior.
 *          Used by HExpr::normalizeConditions to wrap escaped column keys.
 *          OpExpr::doBuild must NOT parenthesize instances of this class.
 */
class ColumnExpr extends Expr {}
// endregion CLASS_ColumnExpr
