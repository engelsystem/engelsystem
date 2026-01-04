<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful;

class Controller
{
    public bool $handled = false;

    public function handle(): string
    {
        $this->handled = true;
        return 'done';
    }
}
