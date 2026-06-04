# Quick Start Guide

## Installation

```bash
composer require andrew-gos/query-builder
```

## Your First Query

```php
use AndrewGos\QueryBuilder\Grammar\DefaultGrammar;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;

$grammar = new DefaultGrammar();

$query = (new SelectQuery())
    ->select(['id', 'name', 'email'])
    ->from(['users'])
    ->where(['active' => true]);

$built = $grammar->buildSelectQuery($query);

echo $built->sql;
// SELECT "id", "name", "email" FROM "users" WHERE "active" IS TRUE
```

## Architecture Overview

Every query flows through a consistent pipeline:

1. **Build a Query object** — use one of the 5 query classes: `SelectQuery`, `InsertQuery`, `UpdateQuery`, `DeleteQuery`, `ValuesQuery`.
2. **Configure clauses** — call fluent methods like `->select()`, `->from()`, `->where()`, `->join()`, etc.
3. **Build with a Grammar** — pass the query to a grammar's `buildXxxQuery()` method. The grammar returns a `BuiltQuery` DTO.
4. **Execute** — use the `BuiltQuery->sql` string and `BuiltQuery->params` array with your database driver (PDO, etc.).

```php
$pdo = new PDO('pgsql:host=localhost;dbname=mydb');
$stmt = $pdo->prepare($built->sql);
$stmt->execute($built->params);
$rows = $stmt->fetchAll();
```

## Choosing a Grammar

| Dialect | Class | Identifier Escaping |
|---|---|---|
| ANSI SQL / Default | `DefaultGrammar` | Double quotes (`"table"`) |
| MySQL | `MySqlGrammar` | Backticks (`` `table` ``) |
| PostgreSQL | `PgSqlGrammar` | Double quotes (`"table"`) + dialect extensions |

Each grammar also requires the corresponding query class for dialect-specific features.

```php
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Query\Select\MySql\MySqlSelectQuery;

$grammar = new MySqlGrammar();
$query = (new MySqlSelectQuery())
    ->select(['id'])
    ->from(['users'])
    ->highPriority()       // MySQL-specific
    ->sqlCalcFoundRows();  // MySQL-specific

echo $grammar->buildSelectQuery($query)->sql;
// SELECT HIGH_PRIORITY SQL_CALC_FOUND_ROWS `id` FROM `users`
```

## Next Steps

- Learn all [query types](query-types.md) available.
- Explore the [expression system](expressions.md) for advanced conditions.
- Check [dialect specifics](dialects.md) for MySQL and PostgreSQL features.
