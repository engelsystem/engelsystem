<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful;

use Engelsystem\Plugins\Plugin;

class TestPluginStateful extends Plugin
{
    public bool $booted = false;
    public bool $installed = false;
    public bool $uninstalled = false;
    public bool $updated = false;
    public bool $enabled = false;
    public bool $disabled = false;

    public function boot(): void
    {
        $this->booted = true;
    }

    public function install(): void
    {
        $this->installed = true;
    }

    public function uninstall(): void
    {
        $this->uninstalled = true;
    }

    public function update(string $from): void
    {
        $this->updated = true;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->disabled = true;
    }
}
