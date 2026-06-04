# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed

- Remove excessive parentheses around column identifiers in short-syntax WHERE/HAVING/JOIN conditions (`['column' => value]`). Added `ColumnExpr` marker class to distinguish simple column identifiers from complex expressions.
- Remove redundant double parentheses in VALUES row lists when row values are arrays (`VALUES ((1, 'Bob'))` → `VALUES (1, 'Bob')`).

### Added

- Documentation: README, quick-start, dialects, expressions, query-types guides.
- CI pipeline: GitHub Actions with PHPUnit and PHP-CS-Fixer.
- `.gitattributes` export-ignore configuration for distribution builds.
- MERGE query support (in development).
- Additional SQL dialect implementations (in development).

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

[Unreleased]: https://github.com/CiBeRHeMuL/QueryBuilder/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/CiBeRHeMuL/QueryBuilder/releases/tag/v1.0.0
