<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Define MERGE action interfaces and value objects for WHEN MATCHED / WHEN NOT MATCHED / BY SOURCE clauses.
 * @scope Sub-namespace partitioning: action interfaces, action classes, clause wrappers, PgSql sub-namespace.
 * @input Action definitions (UPDATE, DELETE, INSERT, DO NOTHING) and clause wrappers.
 * @output Typed MERGE clause value objects ready for query consumption.
 * @modulemap
 * MergeWhenMatchedActionInterface => Contract for WHEN MATCHED / BY SOURCE actions
 * MergeWhenNotMatchedActionInterface => Contract for WHEN NOT MATCHED actions
 * MergeActionUpdate => UPDATE SET action
 * MergeActionDelete => DELETE action
 * MergeActionInsert => INSERT action
 * MergeWhenMatchedClause => WHEN MATCHED clause wrapper with static factories
 * MergeWhenNotMatchedClause => WHEN NOT MATCHED clause wrapper with static factory
 * MergeWhenNotMatchedBySourceClause => WHEN NOT MATCHED BY SOURCE clause wrapper with static factories
 * PgSql/ => PostgreSQL-specific merge actions (DO NOTHING)
 * @usecases
 * - [MergeWhenMatchedClause::update()]: Developer → Configure WHEN MATCHED UPDATE → MERGE query
 * - [MergeWhenNotMatchedClause::insert()]: Developer → Configure WHEN NOT MATCHED INSERT → MERGE query
 */
