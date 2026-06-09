# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- MERGE query support (ANSI SQL:2008 + PostgreSQL extensions: RETURNING, DO NOTHING). New interface: `MergeQueryInterface`. New classes: `MergeQuery`, `PgSqlMergeQuery`, `MergeActionUpdate`, `MergeActionDelete`, `MergeActionInsert`, `PgSqlMergeActionDoNothing`, `MergeWhenMatchedClause`, `MergeWhenNotMatchedClause`, `MergeWhenNotMatchedBySourceClause`.
- Comprehensive MERGE test coverage: INSERT with typed values, UPDATE SET with SelectQuery/ValuesQuery/SetClause[], USING with ValuesQuery/ExprInterface, DefaultGrammar MERGE, MERGE without WHEN clauses, multiple BY SOURCE clauses, ON with ExprInterface, PgSql MERGE without RETURNING.

### Changed

- Documentation: quick-start (5 → 6 query classes), dialects (PgSql MERGE feature table), GrammarInterface (`@input` includes `MergeQueryInterface`), query-types (string semantics warning for MERGE actions).

## [1.1.0] - 2026-06-08

### Added

- Shorthand CTE syntax: `MaybeReturnableQueryInterface` (SelectQuery, ValuesQuery) can now be passed directly to `with()` / `addWith()` without manual `new WithQuery(...)` wrapping. Normalization is handled automatically in `WithTrait`.

## [1.0.1] - 2026-06-08

### Fixed

- Fix `AbstractGrammar::buildDistinctClause` passing `bool` instead of `'DISTINCT'` string to `Expr` constructor, causing broken SQL in all non-PgSql dialects.
- Fix `MySqlGrammar::buildLimitClause` crashing on `ExprInterface` offset/limit values by using `ValueBuilder` for safe expression rendering.
- Fix `andWhere` and `andHaving` overwriting conditions with identical string keys via `array_merge` — now wrap in `AndExpr` to preserve all conditions.
- Fix `BoolOpsExpr::doBuild` crashing on empty conditions array with explicit `QueryBuilderException::emptyBoolExpression()`.
- Fix duplicate validation logic in `buildUpdateQuery` by extracting `validateUpdateQuery()` into `AbstractGrammar`.
- Fix `OpExpr::doBuild` using `$params ??=` instead of explicit `$params =` assignment.
- Fix `HExpr::testSelectExpr` duplicating `testExpr` by delegating the call.
- Fix `PgSqlSelectQuery::addDistinctOn` using assignment instead of `array_merge`, causing column replacement instead of appending.
- CROSS JOIN больше не требует и не допускает условий соединения (ON clause), согласно стандарту SQL.
- Remove redundant parentheses around column identifiers in short-syntax JOIN conditions (`['column' => value]`). String values in join condition arrays are now wrapped in `ColumnExpr` instead of `Expr`, preventing unnecessary parenthesization in `OpExpr::doBuild()`.
- Fix `buildWithClause` to separate multiple CTEs with a comma (`, `) instead of a space (` `), correcting generated SQL for multi-CTE queries.

### Added

- Comprehensive DISTINCT/DISTINCT ON tests: 7 new test methods covering AbstractGrammar, PgSqlGrammar, multiple columns, Expr objects, `distinct(false)` clearing invariant, `addDistinctOn` appending, and toggle on/off.

## [1.0.0] - 2026-06-04

### Added

- SELECT query building with all standard clauses: columns, aliases, DISTINCT, FROM, JOIN (INNER, LEFT, RIGHT, FULL, CROSS), WHERE, GROUP BY, HAVING, WINDOW functions, ORDER BY, LIMIT/OFFSET, locking (FOR UPDATE/FOR SHARE/SKIP LOCKED/NOWAIT).
- INSERT query building with column and value specification.
- UPDATE query building with SET clauses and WHERE conditions.
- DELETE query building with WHERE conditions.
- VALUES query building for row-constructor expressions.
- Set operations (UNION, UNION ALL, INTERSECT, EXCEPT) with parenthesization support.
- Common Table Expressions (WITH clause) including recursive CTEs.
- SQL expression system: `Expr`, `Literal`, `OpExpr`, `AndExpr`, `OrExpr`, `InExpr`, `BoolOpsExpr`.
- Three SQL dialect grammars:
  - `DefaultGrammar` — ANSI SQL with double-quote identifier escaping.
  - `MySqlGrammar` — MySQL dialect with backtick escaping, PARTITION, DELETE/INSERT/UPDATE modifiers, MySQL-specific lock modes.
  - `PgSqlGrammar` — PostgreSQL dialect with RETURNING, DISTINCT ON, ONLY modifier, USING clause, CTE materialization (SEARCH/CYCLE), PostgreSQL-specific lock modes.
- `ValueBuilder` — automatic type dispatch for values (scalars, enums, expressions, sub-queries, arrays).
- Named parameter binding with automatic parameter ID generation.
- Fluent trait-based architecture: `WithTrait`, `FromTrait`, `WhereTrait`, `JoinTrait`, `OperationsTrait`, `OrderByTrait`, `LimitTrait`.
- Support for PHP 8.5+ with property hooks and promoted properties.
- Documentation: README, quick-start, dialects, expressions, query-types guides.
- CI pipeline: GitHub Actions with PHPUnit and PHP-CS-Fixer.
- `.gitattributes` export-ignore configuration for distribution builds.
- MERGE query support (in development).
- Additional SQL dialect implementations (in development).

### Fixed

- Fix `OpExpr` to convert `!=`/`<> NULL/TRUE/FALSE` to `IS NOT NULL/TRUE/FALSE` instead of generating invalid SQL.
- Remove redundant double parentheses in VALUES row lists when row values are arrays (`VALUES ((1, 'Bob'))` → `VALUES (1, 'Bob')`).
- Fix test regex patterns to expect single parentheses in VALUES output, matching corrected production code.
- Remove excessive parentheses around column identifiers in short-syntax WHERE/HAVING conditions (`['column' => value]`). Added `ColumnExpr` marker class to distinguish simple column identifiers from complex expressions.

[Unreleased]: https://github.com/CiBeRHeMuL/QueryBuilder/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/CiBeRHeMuL/QueryBuilder/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/CiBeRHeMuL/QueryBuilder/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/CiBeRHeMuL/QueryBuilder/releases/tag/v1.0.0
