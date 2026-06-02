<?php

/**
 * @moduleContract
 * @purpose Defines the contract for lock mode SQL generation (FOR UPDATE, FOR SHARE, etc.).
 * @scope Interface for grammar-specific lock clause rendering.
 * @input GrammarInterface.
 * @output Lock mode SQL string.
 * @modulemap
 * LockModeInterface => Contract for lock mode SQL generation
 * @usecases
 * - [LockModeInterface]: Grammar → Generate lock SQL → FOR UPDATE/FOR SHARE clause
 */
