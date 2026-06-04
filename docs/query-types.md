# Query Types

QueryBuilder supports 5 query types: SELECT, INSERT, UPDATE, DELETE, and VALUES. Each type is built using its corresponding query class and rendered by a grammar.

---

## SELECT

**Class:** `AndrewGos\QueryBuilder\Query\Select\SelectQuery`

### Basic SELECT

```php
$query = (new SelectQuery())
    ->select(['id', 'name', 'email'])
    ->from(['users']);

// SELECT "id", "name", "email" FROM "users"
```

### WITH columns aliases

```php
$query->select(['user_id' => 'id', 'user_name' => 'name']);

// SELECT "id" AS "user_id", "name" AS "user_name"
```

### DISTINCT

```php
$query->select(['name'])->from(['users'])->distinct();

// SELECT DISTINCT "name" FROM "users"
```

### WHERE conditions

```php
$query->select(['id'])->from(['users'])
    ->where(['active' => true])
    ->andWhere(['age' => 18])
    ->orWhere(['name' => 'admin']);

// WHERE "active" IS TRUE AND "age" = :v1_1 OR "name" = :v1_2
```

### GROUP BY and HAVING

```php
use AndrewGos\QueryBuilder\Expr\Expr;

$query->select(['dept_id', new Expr('COUNT(*)')])
    ->from(['employees'])
    ->groupBy(['dept_id'])
    ->having([new Expr('COUNT(*) > 1')]);

// GROUP BY "dept_id" HAVING COUNT(*) > 1
```

### ORDER BY

```php
$query->select(['id'])->from(['users'])
    ->orderBy(['name' => SORT_ASC, 'created_at' => SORT_DESC]);

// ORDER BY "name" ASC, "created_at" DESC
```

### LIMIT and OFFSET

```php
$query->select(['id'])->from(['users'])
    ->offset(5)
    ->limit(10);

// OFFSET 5 FETCH NEXT 10 ROWS ONLY
```

### JOINs

```php
use AndrewGos\QueryBuilder\Expr\Expr;

$query->select(['u.id', 'p.title'])
    ->from(['users u'])
    ->innerJoin('posts p', ['u.id' => new Expr('p.user_id')]);

// FROM "users u" INNER JOIN "posts p" ON "u"."id" = "p"."user_id"
```

Available join methods: `innerJoin()`, `leftJoin()`, `rightJoin()`, `crossJoin()`, `fullJoin()`, `naturalJoin()`, `naturalInnerJoin()`, `naturalLeftJoin()`, `naturalRightJoin()`, `naturalFullJoin()`.

### WINDOW Functions

```php
use AndrewGos\QueryBuilder\Expr\Window\Window;
use AndrewGos\QueryBuilder\Expr\Window\Over;

$window = (new Window())
    ->partitionBy(['department'])
    ->orderBy(['salary' => SORT_DESC]);

$query->select([
    'name',
    'salary',
    'rank' => new Over(new Expr('RANK()'), $window),
])->from(['employees']);

// SELECT "name", "salary", RANK() OVER (PARTITION BY "department" ORDER BY "salary" DESC) FROM "employees"
```

### Set Operations

```php
$q1 = (new SelectQuery())->select(['id'])->from(['users']);
$q2 = (new SelectQuery())->select(['id'])->from(['admins']);

$q1->unionAll($q2);
// SELECT "id" FROM "users" UNION ALL (SELECT "id" FROM "admins")

$q1->intersectAll($q2);
$q1->exceptAll($q2);
$q1->unionDistinct($q2);
$q1->intersectDistinct($q2);
$q1->exceptDistinct($q2);
```

### Locking

```php
use AndrewGos\QueryBuilder\Enum\Lock\MySql\MySqlLockModeEnum;
use AndrewGos\QueryBuilder\Expr\Lock\MySql\MySqlLockMode;

$query->lock(new MySqlLockMode(MySqlLockModeEnum::ForUpdate));
// FOR UPDATE NOWAIT
```

---

## INSERT

**Class:** `AndrewGos\QueryBuilder\Query\Insert\InsertQuery`

### INSERT with VALUES

```php
$query = (new InsertQuery())
    ->into('users', ['name', 'email'])
    ->source((new ValuesQuery())->values([['Alice', 'alice@x.com'], ['Bob', 'bob@x.com']]));

// INSERT INTO "users" ("name", "email") VALUES ('Alice', 'alice@x.com'), ('Bob', 'bob@x.com')
```

### INSERT with DEFAULT VALUES

```php
$query->into('logs', ['created_at'])->source(null);

// INSERT INTO "logs" ("created_at") DEFAULT VALUES
```

### INSERT with SELECT

```php
$source = (new SelectQuery())->select(['name', 'email'])->from(['invited_users']);
$query->into('users', ['name', 'email'])->source($source);

// INSERT INTO "users" ("name", "email") SELECT "name", "email" FROM "invited_users"
```

### PostgreSQL INSERT with ON CONFLICT

```php
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Expr\Update\SetClause;
use AndrewGos\QueryBuilder\Query\Insert\PgSql\PgSqlInsertQuery;
use AndrewGos\QueryBuilder\Expr\Conflict\PgSql\PgSqlConflictTargetColumns;
use AndrewGos\QueryBuilder\Expr\Conflict\PgSql\PgSqlConflictActionDoUpdate;

$insert = new PgSqlInsertQuery();
$insert->into('users', ['email', 'name'])
    ->source(/* ValuesQuery or SelectQuery */)
    ->onConflict(
        new PgSqlConflictTargetColumns(['email']),
        new PgSqlConflictActionDoUpdate([new SetClause('name', new Expr('EXCLUDED.name'))]),
    );

// ON CONFLICT ("email") DO UPDATE SET "name" = EXCLUDED.name
```

### PostgreSQL INSERT with RETURNING

All PgSql INSERT queries support `RETURNING`:

```php
// INSERT INTO "users" (...) VALUES (...) RETURNING id
```

---

## UPDATE

**Class:** `AndrewGos\QueryBuilder\Query\Update\UpdateQuery`

### Basic UPDATE

```php
$query = (new UpdateQuery())
    ->table('users')
    ->set([
        'name' => 'Alice',
        'email' => 'alice@new.com',
        'age' => 31,
    ])
    ->where(['id' => 42]);

// UPDATE "users" SET "name" = :v1_1, "email" = :v1_2, "age" = :v1_3 WHERE "id" = :v1_4
```

### Expressions in SET

```php
use AndrewGos\QueryBuilder\Expr\Expr;

$query->table('users')->set([
    'counter' => new Expr('counter + 1'),
]);

// UPDATE "users" SET "counter" = counter + 1
```

### MySQL UPDATE with modifiers

```php
use AndrewGos\QueryBuilder\Query\Update\MySql\MySqlUpdateQuery;

$query = (new MySqlUpdateQuery())
    ->table('users')
    ->lowPriority()
    ->set(['name' => 'Alice'])
    ->where(['id' => 1]);

// UPDATE LOW_PRIORITY "users" SET "name" = :v1_1 WHERE "id" = :v1_2
```

### PostgreSQL UPDATE with RETURNING

```php
use AndrewGos\QueryBuilder\Query\Update\PgSql\PgSqlUpdateQuery;

$query = (new PgSqlUpdateQuery())
    ->table('users')
    ->set(['status' => 'inactive'])
    ->where(['last_login < NOW() - INTERVAL 1 year']);

// UPDATE "users" SET "status" = :v1_1 WHERE "last_login < NOW() - INTERVAL 1 year" RETURNING id
```

---

## DELETE

**Class:** `AndrewGos\QueryBuilder\Query\Delete\DeleteQuery`

### Basic DELETE

```php
$query = (new DeleteQuery())
    ->from(['users'])
    ->where(['active' => false]);

// DELETE FROM "users" WHERE "active" IS FALSE
```

### PostgreSQL DELETE with USING and RETURNING

```php
use AndrewGos\QueryBuilder\Expr\Expr;
use AndrewGos\QueryBuilder\Query\Delete\PgSql\PgSqlDeleteQuery;

$query = (new PgSqlDeleteQuery())
    ->from(['users'])
    ->using(['logs'])
    ->where(['logs.user_id' => new Expr('users.id')]);

// DELETE FROM "users" USING "logs" WHERE "logs"."user_id" = "users"."id" RETURNING id
```

### MySQL DELETE with modifiers

```php
use AndrewGos\QueryBuilder\Query\Delete\MySql\MySqlDeleteQuery;

$query = (new MySqlDeleteQuery())
    ->from(['users'])
    ->where(['id' => 10])
    ->quick()
    ->ignore();

// DELETE QUICK IGNORE FROM "users" WHERE "id" = :v1_1
```

---

## VALUES

**Class:** `AndrewGos\QueryBuilder\Query\Values\ValuesQuery`

### Basic VALUES

```php
$query = (new ValuesQuery())
    ->values([[1, 'Alice'], [2, 'Bob']]);

// VALUES (1, 'Alice'), (2, 'Bob')
```

### VALUES with ORDER BY and LIMIT

```php
use AndrewGos\QueryBuilder\Expr\Order\OrderColumn;

$query->values([[1, 'Alice'], [2, 'Bob']])
    ->orderBy([new OrderColumn('1', 'DESC')])
    ->limit(10);

// VALUES (1, 'Alice'), (2, 'Bob') ORDER BY 1 DESC FETCH FIRST 10 ROWS ONLY
```

---

## Fluent Method Reference

All query objects use fluent methods (returning `static`) for method chaining.

### Trait Methods

| Trait | Methods |
|---|---|
| `WithTrait` | `with(array, bool $recursive = false)`, `addWith(array, bool $recursive = false)` |
| `WhereTrait` | `where(array\|ExprInterface)`, `andWhere(...)`, `orWhere(...)` |
| `FromTrait` | `from(array)`, `addFrom(array)` |
| `JoinTrait` | `join(...)`, `innerJoin(...)`, `leftJoin(...)`, `rightJoin(...)`, `crossJoin(...)`, `fullJoin(...)`, plus 6 natural variants |
| `OperationsTrait` | `operateWith(SetOperationEnum, SelectQueryInterface...)`, `unionAll(...)`, `intersectAll(...)`, `exceptAll(...)`, `unionDistinct(...)`, `intersectDistinct(...)`, `exceptDistinct(...)` |
| `OrderByTrait` | `orderBy(array)`, `addOrderBy(array)` |
| `LimitTrait` | `offset(int)`, `limit(int, ?LimitBoundTypeEnum)` |

### Query-Type Composition

| Query | With | From | Where | Join | Operations | OrderBy | Limit |
|---|---|---|---|---|---|---|---|
| SelectQuery | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| InsertQuery | ✅ | — | — | — | — | — | — |
| UpdateQuery | ✅ | — | ✅ | — | — | — | — |
| DeleteQuery | ✅ | ✅* | ✅ | — | — | — | — |
| ValuesQuery | — | — | — | — | ✅ | ✅ | ✅ |

*DeleteQuery uses `SingleFromTrait` (only first table entry).
