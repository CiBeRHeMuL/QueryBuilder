<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose PostgreSQL-specific MERGE actions (DO NOTHING).
 * @scope PgSql sub-namespace for MERGE expression objects.
 * @input None
 * @output PgSql-specific merge action classes.
 * @modulemap
 * PgSqlMergeActionDoNothing => DO NOTHING action (implements both action interfaces)
 * @usecases
 * - [PgSqlMergeActionDoNothing]: Developer → Skip matched/not-matched row → MERGE query
 */
