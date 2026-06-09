# SQL Dialects

QueryBuilder supports three SQL dialects: ANSI/Default, MySQL, and PostgreSQL. Each dialect has its own identifier escaping rules and dialect-specific SQL features.

---

## DefaultGrammar (ANSI SQL)

**Class:** `AndrewGos\QueryBuilder\Grammar\Default\DefaultGrammar`

The default grammar generates standard ANSI SQL:

- **Identifiers** escaped with double quotes: `"users"`, `"id"`.
- **Wildcard** `*` is returned unquoted.
- **LIMIT / OFFSET** uses ANSI syntax: `FETCH FIRST n ROWS ONLY`.

A lightweight, dependency-free fallback grammar with no vendor-specific extensions.

---

## MySqlGrammar

**Class:** `AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar`

Requires MySQL-specific query classes for full feature access:

| Feature | Query Class | Description |
|---|---|---|
| SELECT hints | `MySqlSelectQuery` | `HIGH_PRIORITY`, `STRAIGHT_JOIN`, `SQL_SMALL_RESULT`, `SQL_BIG_RESULT`, `SQL_BUFFER_RESULT`, `SQL_NO_CACHE`, `SQL_CALC_FOUND_ROWS` |
| DELETE modifiers | `MySqlDeleteQuery` | `LOW_PRIORITY`, `QUICK`, `IGNORE`, `PARTITION` |
| INSERT modifiers | `MySqlInsertQuery` | `LOW_PRIORITY`, `DELAYED`, `HIGH_PRIORITY`, `IGNORE`, `PARTITION` |
| UPDATE modifiers | `MySqlUpdateQuery` | `LOW_PRIORITY`, `IGNORE`, custom ORDER BY, LIMIT, PARTITION |

**Identifier escaping:** backticks (`` ` ``).
**LIMIT syntax:** `LIMIT offset, count` (ANSI compatibility offset defaults to `18446744073709551615` when limit is used without offset).
**Lock modes:** `FOR UPDATE` / `FOR SHARE` via `MySqlLockMode` with `NOWAIT` / `SKIP LOCKED`.

```php
use AndrewGos\QueryBuilder\Grammar\MySql\MySqlGrammar;
use AndrewGos\QueryBuilder\Query\Select\MySql\MySqlSelectQuery;

$grammar = new MySqlGrammar();
$query = (new MySqlSelectQuery())
    ->select(['id', 'name'])
    ->from(['users'])
    ->highPriority()
    ->straightJoin()
    ->sqlCalcFoundRows();

echo $grammar->buildSelectQuery($query)->sql;
// SELECT HIGH_PRIORITY STRAIGHT_JOIN SQL_CALC_FOUND_ROWS `id`, `name` FROM `users`
```

---

## PgSqlGrammar (PostgreSQL)

**Class:** `AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar`

The most feature-rich dialect with PostgreSQL-specific extensions:

| Feature | Query Class | Description |
|---|---|---|
| DISTINCT ON | `PgSqlSelectQuery` | `SELECT DISTINCT ON (columns) ...` |
| ONLY table modifier | `PgSqlSelectTable` | `SELECT ... FROM ONLY table` (table inheritance) |
| CTE materialization | `PgSqlWithQuery` | `MATERIALIZED` / `NOT MATERIALIZED` |
| RETURNING | All PgSql queries | `RETURNING *` with optional `WITH (OLD AS ..., NEW AS ...)` aliases |
| ON CONFLICT | `PgSqlInsertQuery` | Upsert: `ON CONFLICT (columns) DO NOTHING / DO UPDATE` |
| USING clause | `PgSqlDeleteQuery` | `DELETE FROM ... USING ...` |
| FROM + JOIN in UPDATE | `PgSqlUpdateQuery` | `UPDATE ... FROM ... JOIN ... SET ...` |
| MERGE | `PgSqlMergeQuery` | `MERGE INTO ... USING ... ON ... WHEN MATCHED ... WHEN NOT MATCHED ... WHEN NOT MATCHED BY SOURCE ...` with RETURNING / BY SOURCE |
| SEARCH / CYCLE | `PgSqlWithQuery` | CTE cycle detection: `SEARCH BREADTH|DEPTH FIRST BY cols SET seq_col` / `CYCLE cols SET mark_col TO value` |

**Identifier escaping:** double quotes (`"`), same as DefaultGrammar.
**Lock modes:** `FOR UPDATE`, `FOR NO KEY UPDATE`, `FOR SHARE`, `FOR KEY SHARE` via `PgSqlLockMode` with `NOWAIT` / `SKIP LOCKED` and `OF tables`.

```php
use AndrewGos\QueryBuilder\Grammar\PgSql\PgSqlGrammar;
use AndrewGos\QueryBuilder\Query\Select\PgSql\PgSqlSelectQuery;

$grammar = new PgSqlGrammar();
$query = (new PgSqlSelectQuery())
    ->select(['id', 'name'])
    ->from(['users'])
    ->distinctOn(['name']);

echo $grammar->buildSelectQuery($query)->sql;
// SELECT DISTINCT ON ("name") "id", "name" FROM "users"
```

### CTE with MATERIALIZED (PostgreSQL)

```php
use AndrewGos\QueryBuilder\Expr\Cte\PgSql\PgSqlWithQuery;

$inner = (new SelectQuery())->select(['id'])->from(['users']);
$cte = new PgSqlWithQuery($inner, materialized: true);

$query = (new SelectQuery())
    ->with(['active_users' => $cte])
    ->select(['id'])
    ->from(['active_users']);

// WITH "active_users" AS MATERIALIZED ( SELECT "id" FROM "users" ) SELECT "id" FROM "active_users"
```

### INSERT with ON CONFLICT

```php
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Query\Insert\PgSql\PgSqlInsertQuery;
use AndrewGos\QueryBuilder\Expr\Conflict\PgSql\PgSqlConflictTargetColumns;
use AndrewGos\QueryBuilder\Expr\Conflict\PgSql\PgSqlConflictActionDoUpdate;
use AndrewGos\QueryBuilder\Query\Values\ValuesQuery;

$insert = new PgSqlInsertQuery();
$insert->into('users', ['name', 'email'])
    ->source((new ValuesQuery())->values([['Alice', 'a@x.com']]))
    ->onConflict(
        new PgSqlConflictTargetColumns(['email']),
        new PgSqlConflictActionDoUpdate([new SetClause('name', new Expr('EXCLUDED.name'))]),
    );

// INSERT INTO "users" ("name", "email") VALUES ('Alice', 'a@x.com') ON CONFLICT ("email") DO UPDATE SET "name" = EXCLUDED.name
```

---

## When to Use Which Dialect

- **DefaultGrammar** — When you need portable ANSI SQL or a simple fallback.
- **MySqlGrammar** — When targeting MySQL / MariaDB databases.
- **PgSqlGrammar** — When targeting PostgreSQL databases and need advanced features like RETURNING, ON CONFLICT, or CTE materialization.
