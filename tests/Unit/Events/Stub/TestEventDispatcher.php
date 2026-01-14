<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events\Stub;

class TestEventDispatcher
{
    public function handle(): array
    {
        return ['default' => 'handler'];
    }
}
