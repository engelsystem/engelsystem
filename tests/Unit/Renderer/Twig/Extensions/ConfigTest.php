<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Config\Config as EngelsystemConfig;
use Engelsystem\Renderer\Twig\Extensions\Config;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Config::class, '__construct')]
#[CoversMethod(Config::class, 'getFunctions')]
class ConfigTest extends ExtensionTestCase
{
    public function testGetFunctions(): void
    {
        $config = $this->createStub(EngelsystemConfig::class);

        $extension = new Config($config);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('config', [$config, 'get'], $functions);
    }
}
