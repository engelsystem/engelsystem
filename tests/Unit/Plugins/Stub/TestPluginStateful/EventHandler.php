<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful;

class EventHandler
{
    public bool $handled = false;

    public function handle(): void
    {
        $this->handled = true;
    }
}
