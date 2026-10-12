<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Color as ColorHelper;
use Engelsystem\Renderer\Twig\Extensions\Color;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Color::class, 'getFunctions')]
#[CoversMethod(Color::class, 'getColor')]
class ColorTest extends ExtensionTestCase
{
    public function testGetGlobals(): void
    {
        $extension = new Color();
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('color', [$extension, 'getColor'], $functions);
    }

    public function testGetColor(): void
    {
        $extension = new Color();

        $value = $extension->getColor(null);
        $this->assertInstanceOf(ColorHelper::class, $value);
        $this->assertEquals('#000000', (string) $value);

        $value = $extension->getColor('#123');
        $this->assertEquals('#112233', (string) $value);
    }
}
