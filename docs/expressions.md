# Expressions

QueryBuilder provides a flexible expression system for building SQL conditions, values, and fragments. All expressions implement `ExprInterface`.

---

## Expression Types

### Expr — Raw SQL Fragment

The simplest expression — holds a pre-built SQL string with optional parameters.

```php
use AndrewGos\QueryBuilder\Expr\Expr;

$expr = new Expr('COUNT(*) > 1');
// Renders as: COUNT(*) > 1
```

### Literal — Parameterized Value

Wraps a scalar value (`int`, `float`, `string`) and generates a named parameter placeholder (`:v1_1`).

```php
use AndrewGos\QueryBuilder\Expr\Literal;

$expr = new Literal(42);
// Renders as: :v1_1  (with params: [':v1_1' => 42])
```

### ColumnExpr — Column Identifier Marker

Extends `Expr` to mark a value as a column identifier. Used internally by conditions to distinguish column references from values.

### OpExpr — Binary Operator

Builds a comparison: `left OPERATOR right`. Auto-handles special cases:

- `= NULL` → `IS NULL`
- `<> NULL` → `IS NOT NULL`
- `= TRUE` → `IS TRUE`
- `<> TRUE` → `IS NOT TRUE`

```php
use AndrewGos\QueryBuilder\Expr\OpExpr;

// WHERE age = 25
$expr = new OpExpr('age', '=', 25);
```

### InExpr — IN / NOT IN

Extends `OpExpr` with `IN` / `NOT IN` support. The right side can be:

- An array of values
- An `ExprInterface`
- A subquery (`SelectQueryInterface`)

```php
use AndrewGos\QueryBuilder\Expr\InExpr;
use AndrewGos\QueryBuilder\Query\Select\SelectQuery;

// WHERE id IN (1, 2, 3)
$expr = new InExpr('id', 'IN', [1, 2, 3]);

// WHERE id NOT IN (SELECT user_id FROM banned)
$subquery = (new SelectQuery())->select(['user_id'])->from(['banned']);
$expr = new InExpr('id', 'NOT IN', $subquery);
```

### AndExpr / OrExpr — Boolean Conditions

Combine multiple conditions with `AND` or `OR` logic.

```php
use AndrewGos\QueryBuilder\Expr\AndExpr;
use AndrewGos\QueryBuilder\Expr\OrExpr;

// WHERE active = TRUE AND age > 18
$expr = new AndExpr(['active' => true, new OpExpr('age', '>', 18)]);

// WHERE active = TRUE OR deleted = FALSE
$expr = new OrExpr(['active' => true, 'deleted' => false]);
```

Conditions are normalized automatically: short syntax `['col' => value]` is converted to `OpExpr('col', '=', value)`.

---

## Short-Syntax Conditions

When using `where()`, `having()`, or condition arrays, you can use short syntax for convenience:

```php
// All of these are equivalent:
$query->where(['active' => true]);
$query->where([new OpExpr('active', '=', true)]);
$query->where([new OpExpr('active', 'IS', true)]);
```

Array values become `IN` expressions, subqueries are wrapped in parentheses:

```php
// WHERE id IN (1, 2, 3)
$query->where(['id' => [1, 2, 3]]);

// WHERE id IN (SELECT user_id FROM banned)
$query->where(['id' => $subquery]);
```

---

## ValueBuilder

`ValueBuilder` is a type dispatcher that converts any PHP value into an `ExprInterface`:

| Input Type | Output |
|---|---|
| `null` | `Expr('NULL')` |
| `true` / `false` | `Expr('TRUE')` / `Expr('FALSE')` |
| `int` / `float` / `string` | `Literal` with named parameter |
| `ExprInterface` | Pass-through (already an expression) |
| `BackedEnum` | `Literal` with the backed value |
| `UnitEnum` | `Literal` with the enum name |
| `SelectQueryInterface` | Subquery wrapped in `(...)` |
| `ValuesQueryInterface` | Values subquery wrapped in `(...)` |
| `array` | Recursively builds each element, wrapped in `(elem1, elem2, ...)` |

```php
use AndrewGos\QueryBuilder\Builder\ValueBuilder;

$builder = new ValueBuilder();

$expr = $builder->build(42, $grammar);
// Literal: :v1_1

$expr = $builder->build([1, 2, 3], $grammar);
// (:v1_1, :v1_2, :v1_3)
```

---

## Window Functions

### Window Definition

Define a window with `Window`:

```php
use AndrewGos\QueryBuilder\Expr\Window\Window;

$window = new Window();
$window->partitionBy(['department'])
    ->orderBy(['salary' => SORT_DESC]);
// Default frame: RANGE BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
```

### Over Expression

Wrap a function expression with `Over`:

```php
use AndrewGos\QueryBuilder\Expr\Window\Over;

// RANK() OVER (PARTITION BY department ORDER BY salary DESC)
$query->select([
    'name',
    'salary',
    'rank' => new Over(new Expr('RANK()'), $window),
]);
```

### Named Windows

Define a named window and reference it in OVER:

```php
$query->select(['id', new Expr('row_number() OVER w')])
    ->from(['employees'])
    ->window('w', $window);

// WINDOW "w" AS (PARTITION BY "dept_id" ORDER BY "salary" DESC)
```

### Frame Specifications

```php
$window->frame(
    FrameTypeEnum::Rows,
    FrameBoundEnum::Preceding,     // start
    FrameBoundEnum::CurrentRow,    // end
    5,                             // start offset
);

// ROWS BETWEEN 5 PRECEDING AND CURRENT ROW
```

Convenience methods (each delegates to `frame()` with the corresponding type):

```php
use AndrewGos\QueryBuilder\Enum\Window\FrameBoundEnum;

// RANGE BETWEEN 5 PRECEDING AND 10 FOLLOWING
$window->range(FrameBoundEnum::Preceding, FrameBoundEnum::Following, 5, 10);

// ROWS BETWEEN UNBOUNDED PRECEDING AND 3 FOLLOWING
$window->rows(FrameBoundEnum::Preceding, FrameBoundEnum::Following, null, 3);

// GROUPS BETWEEN 1 PRECEDING AND UNBOUNDED FOLLOWING
$window->groups(FrameBoundEnum::Preceding, FrameBoundEnum::Following, 1, null);
```

---

## Common Table Expressions (CTE)

### Basic CTE

```php
use AndrewGos\QueryBuilder\Expr\Cte\WithQuery;

$inner = (new SelectQuery())->select(['id', 'name'])->from(['users']);
$cte = new WithQuery($inner);

$query = (new SelectQuery())
    ->with(['active_users' => $cte])
    ->select(['id'])
    ->from(['active_users']);

// WITH "active_users" AS ( SELECT "id", "name" FROM "users" ) SELECT "id" FROM "active_users"
```

### Recursive CTE

```php
$query->with(['cte' => new WithQuery($inner)], recursive: true);

// WITH RECURSIVE "cte" AS ( SELECT "id" FROM "users" ) SELECT "id" FROM "cte"
```

### SEARCH and CYCLE (PostgreSQL)

```php
use AndrewGos\QueryBuilder\Enum\Cte\SearchTypeEnum;

$cte = (new WithQuery($inner))
    ->search(SearchTypeEnum::Breadth, ['id', 'parent_id'], 'seq')
    ->cycle(['id'], 'is_cycle', 'path', true, false);

// WITH RECURSIVE "cte" AS (...) SEARCH BREADTH FIRST BY "id", "parent_id" SET "seq" CYCLE "id" SET "is_cycle" TO true DEFAULT false USING "path"
```

### Materialized CTE (PostgreSQL)

```php
use AndrewGos\QueryBuilder\Expr\Cte\PgSql\PgSqlWithQuery;

$cte = new PgSqlWithQuery($inner, materialized: true);
// WITH "cte" AS MATERIALIZED ( ... )
```

---

## Lock Modes

Row-level locking is dialect-specific:

```php
// PostgreSQL: FOR UPDATE / FOR NO KEY UPDATE / FOR SHARE / FOR KEY SHARE
use AndrewGos\QueryBuilder\Enum\Lock\PgSql\PgSqlLockModeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\PgSql\PgSqlLockWaitModeEnum;
use AndrewGos\QueryBuilder\Expr\Lock\PgSql\PgSqlLockMode;

$query->lock(new PgSqlLockMode(
    PgSqlLockModeEnum::ForUpdate,
    ['users'],
    PgSqlLockWaitModeEnum::SkipLocked,
));
// FOR UPDATE OF "users" SKIP LOCKED
```

```php
// MySQL: FOR UPDATE / FOR SHARE
use AndrewGos\QueryBuilder\Enum\Lock\MySql\MySqlLockModeEnum;
use AndrewGos\QueryBuilder\Enum\Lock\MySql\MySqlLockWaitModeEnum;
use AndrewGos\QueryBuilder\Expr\Lock\MySql\MySqlLockMode;

$query->lock(new MySqlLockMode(
    MySqlLockModeEnum::ForUpdate,
    waitMode: MySqlLockWaitModeEnum::Nowait,
));
// FOR UPDATE NOWAIT
```

---

## Helper Functions (HExpr)

`AndrewGos\QueryBuilder\Helper\HExpr` provides static utilities:

| Method | Purpose |
|---|---|
| `testExpr(mixed)` | Validate value is a valid expression type |
| `testCondition(mixed)` | Validate condition value |
| `normalizeConditions(array, GrammarInterface)` | Convert `['col' => value]` → `OpExpr` array |
| `normalizeOrderBy(array)` | Convert `['col' => SORT_ASC]` → `OrderColumn[]` |
| `normalizeTable(mixed)` | Convert string table → `SelectTable` |
| `mergeParams(array...)` | Merge parameter arrays |
| `mergeExpressionParts(array, GrammarInterface, string)` | Merge mixed expression array into single `Expr` |
