# QueryBuilder — Fluent SQL Query Builder for PHP 8.5

[![CI](https://github.com/CiBeRHeMuL/QueryBuilder/actions/workflows/ci.yml/badge.svg)](https://github.com/CiBeRHeMuL/QueryBuilder/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/andrew-gos/query-builder/version.svg)](https://packagist.org/packages/andrew-gos/query-builder)
[![Latest Unstable Version](https://poser.pugx.org/andrew-gos/query-builder/v/unstable.svg)](https://packagist.org/packages/andrew-gos/query-builder)
[![PHP Version](https://poser.pugx.org/andrew-gos/query-builder/require/php)](https://packagist.org/packages/andrew-gos/query-builder)
[![License](https://poser.pugx.org/andrew-gos/query-builder/license.svg)](https://packagist.org/packages/andrew-gos/query-builder)
[![Total Downloads](https://poser.pugx.org/andrew-gos/query-builder/d/total.svg)](https://packagist.org/packages/andrew-gos/query-builder)
[![Codecov](https://codecov.io/github/CiBeRHeMuL/QueryBuilder/graph/badge.svg)](https://codecov.io/github/CiBeRHeMuL/QueryBuilder)

A lightweight, fluent SQL query builder with multi-dialect support (ANSI, MySQL, PostgreSQL) and an extensible expression system. Write complex SQL queries programmatically without string concatenation.

---

## Quick Start

```php
use AndrewGos\QueryBuilder\Grammar\DefaultGrammar;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;

$grammar = new DefaultGrammar();
$query = (new SelectQuery())
    ->select(['id', 'name', 'email'])
    ->from(['users'])
    ->where(['active' => true]);

$built = $grammar->buildSelectQuery($query);
echo $built->sql;    // SELECT "id", "name", "email" FROM "users" WHERE "active" IS TRUE
print_r($built->params);  // []
```

---

## Features

| Feature | Status |
|---|---|
| SELECT, INSERT, UPDATE, DELETE, VALUES queries | ✅ |
| WHERE, HAVING, GROUP BY, ORDER BY, LIMIT / OFFSET | ✅ |
| JOIN (INNER, LEFT, RIGHT, FULL, CROSS, NATURAL variants) | ✅ |
| Window functions (OVER, PARTITION BY, frame specs) | ✅ |
| Common Table Expressions (WITH, WITH RECURSIVE) | ✅ |
| PostgreSQL: MATERIALIZED CTE, SEARCH / CYCLE | ✅ |
| Set operations: UNION, INTERSECT, EXCEPT (ALL / DISTINCT) | ✅ |
| Row-level locking (FOR UPDATE / FOR SHARE, NOWAIT / SKIP LOCKED) | ✅ |
| MySQL: HIGH_PRIORITY, STRAIGHT_JOIN, SQL_* hints, PARTITION | ✅ |
| PostgreSQL: DISTINCT ON, ONLY modifier, RETURNING, ON CONFLICT | ✅ |
| Named parameter binding with auto-generated IDs | ✅ |
| MERGE query | ✅ |
| PostgreSQL: MERGE RETURNING, DO NOTHING | ✅ |
| Additional SQL dialects | 🚧 In development |

---

## Documentation

- [Quick Start Guide](docs/quick-start.md) — Installation, first query, architecture overview.
- [Query Types](docs/query-types.md) — Full reference for SELECT, INSERT, UPDATE, DELETE, VALUES, MERGE.
- [Expressions](docs/expressions.md) — Expression system, ValueBuilder, conditions, windows, CTEs.
- [Dialects](docs/dialects.md) — Default/ANSI, MySQL, and PostgreSQL grammar specifics.

---

## Requirements

- PHP 8.5+
- `andrew-gos/helpers` ^1.0

---

## Installation

```bash
composer require andrew-gos/query-builder
```

---

## Roadmap

- **v1.2.0** — Additional SQL dialects (SQLite, MariaDB)

---

## License

This library is open-source software licensed under the [MIT License](LICENSE).
