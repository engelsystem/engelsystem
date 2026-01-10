<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Http\UrlGenerator;
use Engelsystem\Renderer\Twig\Extensions\Url;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversMethod(Url::class, '__construct')]
#[CoversMethod(Url::class, 'getFunctions')]
#[CoversMethod(Url::class, 'getUrl')]
class UrlTest extends ExtensionTestCase
{
    public function testGetGlobals(): void
    {
        $urlGenerator = $this->createStub(UrlGenerator::class);

        $extension = new Url($urlGenerator);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('url', [$extension, 'getUrl'], $functions);
    }

    /**
     * @return string[][]
     */
    public static function getUrls(): array
    {
        return [
            ['/', '/', 'https://foo.bar/'],
            ['/foo', '/foo', 'https://foo.bar/foo'],
            ['foo_bar', 'foo-bar', 'https://foo.bar/foo-bar'],
            ['dolor', 'dolor', 'https://foo.bar/dolor?lorem_ipsum=dolor', ['lorem_ipsum' => 'dolor']],
        ];
    }

    #[DataProvider('getUrls')]
    public function testGetUrl(string $url, string $urlTo, string $return, array $parameters = []): void
    {
        $urlGenerator = $this->createMock(UrlGenerator::class);

        $urlGenerator->expects($this->once())
            ->method('to')
            ->with($urlTo, $parameters)
            ->willReturn($return);

        $extension = new Url($urlGenerator);
        $generatedUrl = $extension->getUrl($url, $parameters);

        $this->assertEquals($return, $generatedUrl);
    }
}
