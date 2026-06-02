<?php

/**
 * @moduleContract
 * @purpose Helper namespace — static utility methods for validation, normalization, and merging of SQL expression nodes.
 * @scope Type validation, condition/order-by normalization, parameter merging, expression part merging.
 * @input Mixed values, arrays, GrammarInterface
 * @output Validated/normalized data, merged parameter arrays, ExprInterface
 * @modulemap
 * HExpr [10][Static expression utility class] => HExpr.php
 * @usecases
 * - HExpr: Grammar/Builder → Validate/normalize expressions → Processed data
 */
