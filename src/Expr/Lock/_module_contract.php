<?php

declare(strict_types=1);

/*
 * @moduleContract
 * @purpose Defines the contract for lock mode SQL generation (FOR UPDATE, FOR NO KEY UPDATE, FOR SHARE, FOR KEY SHARE, with optional NOWAIT/SKIP LOCKED).
 * @scope Interface for grammar-specific lock clause rendering.
 * @input GrammarInterface.
 * @output Lock mode SQL string.
 * @modulemap
 * LockModeInterface => Contract for lock mode SQL generation
 * @usecases
 * - [LockModeInterface]: Grammar → Generate lock SQL → FOR UPDATE/FOR SHARE clause
 */
