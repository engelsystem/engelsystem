<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events\Stub;

class TestEventDispatcher
{
    public static bool $handled = false;

    public function handle(): void
    {
        self::$handled = true;
    }
}
