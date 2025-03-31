<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit;

/**
 * Only used to store "global" state during test runtime
 */
abstract class RuntimeTest
{
    public static array $dbState = [];
}
