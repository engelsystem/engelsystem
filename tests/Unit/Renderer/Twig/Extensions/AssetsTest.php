<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Http\UrlGenerator;
use Engelsystem\Renderer\Twig\Extensions\Assets;
use PHPUnit\Framework\MockObject\MockObject;

class AssetsTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Assets::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Assets::getFunctions
     */
    public function testGetGlobals()
    {
        /** @var UrlGenerator|MockObject $urlGenerator */
        $urlGenerator = $this->createMock(UrlGenerator::class);

        $extension = new Assets($urlGenerator);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('asset', [$extension, 'getAsset'], $functions);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Assets::getAsset
     */
    public function testGetAsset()
    {
        /** @var UrlGenerator|MockObject $urlGenerator */
        $urlGenerator = $this->createMock(UrlGenerator::class);

        $urlGenerator->expects($this->exactly(2))
            ->method('to')
            ->with('/assets/foo.css')
            ->willReturn('https://foo.bar/project/assets/foo.css');

        $extension = new Assets($urlGenerator);

        $return = $extension->getAsset('assets/foo.css');
        $this->assertEquals('https://foo.bar/project/assets/foo.css', $return);

        $return = $extension->getAsset('/assets/foo.css');
        $this->assertEquals('https://foo.bar/project/assets/foo.css', $return);
    }
}
