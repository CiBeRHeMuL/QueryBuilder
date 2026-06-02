<?php

namespace AndrewGos\QueryBuilder\Enum\Insert\PgSql;

// region MODULE_CONTRACT [DOMAIN(6): Enum; CONCEPT(7): OverrideValue; TECH(9): PgSQL]
/**
 * @moduleContract
 * @purpose Define OVERRIDING SYSTEM/USER VALUE method for PostgreSQL INSERT.
 * @scope Override value method constants.
 * @input No runtime input — compile-time case selection.
 * @output Case selection for PostgreSQL insert override behavior.
 * @modulemap
 * PgSqlOverrideValueMethodEnum => PostgreSQL OVERRIDING VALUE methods
 */
// endregion MODULE_CONTRACT
// GREP_SUMMARY: PostgreSQL, Insert, Override, System, User, Value

// region ENUM_PgSqlOverrideValueMethodEnum [DOMAIN(6): Enum; CONCEPT(7): OverrideValue; TECH(9): PgSQL]
/**
 * @purpose Represent PostgreSQL INSERT OVERRIDING SYSTEM/USER VALUE method.
 * @io self -> override behavior
 */
enum PgSqlOverrideValueMethodEnum
{
    case System;
    case User;
}
// endregion ENUM_PgSqlOverrideValueMethodEnum
