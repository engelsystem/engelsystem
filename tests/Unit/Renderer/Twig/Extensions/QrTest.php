<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Qr;

class QrTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Qr::getFunctions
     */
    public function testGetGlobals(): void
    {
        $extension = new Qr();
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('qr', [$extension, 'getQr'], $functions, ['is_safe' => ['html']]);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Qr::getQr
     */
    public function testGetQr(): void
    {
        $extension = new Qr();

        $generatedCode = $extension->getQr('Test');
        $this->assertStringContainsString('<svg', $generatedCode);
        $this->assertStringContainsString('width="200" height="200"', $generatedCode);

        $generatedCode = $extension->getQr('Test', 1337);
        $this->assertStringContainsString('width="1337" height="1337"', $generatedCode);
    }
}
