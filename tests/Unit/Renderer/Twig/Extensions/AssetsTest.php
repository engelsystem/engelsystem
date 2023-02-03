<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Assets as AssetsProvider;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Renderer\Twig\Extensions\Assets;
use PHPUnit\Framework\MockObject\MockObject;

class AssetsTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Assets::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Assets::getFunctions
     */
    public function testGetFunctions(): void
    {
        /** @var UrlGenerator&MockObject $urlGenerator */
        $urlGenerator = $this->createMock(UrlGenerator::class);
        /** @var AssetsProvider&MockObject $assets */
        $assets = $this->createMock(AssetsProvider::class);

        $extension = new Assets($assets, $urlGenerator);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('asset', [$extension, 'getAsset'], $functions);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Assets::getAsset
     */
    public function testGetAsset(): void
    {
        /** @var UrlGenerator&MockObject $urlGenerator */
        $urlGenerator = $this->createMock(UrlGenerator::class);
        /** @var AssetsProvider&MockObject $assets */
        $assets = $this->createMock(AssetsProvider::class);

        $urlGenerator->expects($this->exactly(4))
            ->method('to')
            ->withConsecutive(['/test.png'], ['/assets/foo.css'], ['/assets/bar.css'], ['/assets/lorem-hashed.js'])
            ->willReturnCallback(function ($path) {
                return 'https://foo.bar/project' . $path;
            });

        $assets->expects($this->exactly(3))
            ->method('getAssetPath')
            ->withConsecutive(['foo.css'], ['bar.css'], ['lorem.js'])
            ->willReturnOnConsecutiveCalls('foo.css', 'bar.css', 'lorem-hashed.js');

        $extension = new Assets($assets, $urlGenerator);

        $return = $extension->getAsset('test.png');
        $this->assertEquals('https://foo.bar/project/test.png', $return);

        $return = $extension->getAsset('assets/foo.css');
        $this->assertEquals('https://foo.bar/project/assets/foo.css', $return);

        $return = $extension->getAsset('/assets/bar.css');
        $this->assertEquals('https://foo.bar/project/assets/bar.css', $return);

        $return = $extension->getAsset('assets/lorem.js');
        $this->assertEquals('https://foo.bar/project/assets/lorem-hashed.js', $return);
    }
}
