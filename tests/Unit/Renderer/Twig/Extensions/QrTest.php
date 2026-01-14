<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\Qr;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Qr::class, 'getFunctions')]
#[CoversMethod(Qr::class, 'getQr')]
class QrTest extends ExtensionTestCase
{
    public function testGetGlobals(): void
    {
        $extension = new Qr();
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('qr', [$extension, 'getQr'], $functions, ['is_safe' => ['html']]);
    }

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
