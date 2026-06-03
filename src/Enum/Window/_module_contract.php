<?php

declare(strict_types=1);

/**
 * @moduleContract
 * @purpose Provide enum types for SQL window function frame clause building.
 * @scope Window frame type, bound, and exclusion enums.
 * @input Compile-time case selection.
 * @output SQL window frame clause fragment strings via getSql() methods.
 * @modulemap
 * FrameTypeEnum => Window frame types (ROWS, RANGE, GROUPS)
 * FrameBoundEnum => Window frame bounds (PRECEDING, FOLLOWING, CURRENT ROW)
 * FrameExclusionEnum => Window frame exclusions (CURRENT ROW, GROUP, TIES, NO OTHERS)
 * @usecases
 * - WindowBuilder: User -> Build Frame Clause -> FrameTypeEnum, FrameBoundEnum, FrameExclusionEnum cases
 */
