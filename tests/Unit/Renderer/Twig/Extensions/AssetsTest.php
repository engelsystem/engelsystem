<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Assets as AssetsProvider;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Renderer\Twig\Extensions\Assets;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Assets::class, '__construct')]
#[CoversMethod(Assets::class, 'getFunctions')]
#[CoversMethod(Assets::class, 'getAsset')]
class AssetsTest extends ExtensionTestCase
{
    public function testGetFunctions(): void
    {
        $urlGenerator = $this->createStub(UrlGenerator::class);
        $assets = $this->createStub(AssetsProvider::class);

        $extension = new Assets($assets, $urlGenerator);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('asset', [$extension, 'getAsset'], $functions);
    }

    public function testGetAsset(): void
    {
        $urlGenerator = $this->createMock(UrlGenerator::class);
        $assets = $this->createMock(AssetsProvider::class);

        $matcher = $this->exactly(4);
        $urlGenerator->expects($matcher)
            ->method('to')
            ->willReturnCallback(function ($path) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('/test.png', $path);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('/assets/foo.css', $path);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('/assets/bar.css', $path);
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('/assets/lorem-hashed.js', $path);
                }
                return 'https://foo.bar/project' . $path;
            });

        $matcher = $this->exactly(3);
        $assets->expects($matcher)
            ->method('getAssetPath')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('foo.css', $parameters[0]);
                    return 'foo.css';
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('bar.css', $parameters[0]);
                    return 'bar.css';
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('lorem.js', $parameters[0]);
                    return 'lorem-hashed.js';
                }
            });

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
