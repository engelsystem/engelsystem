<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Http\UrlGenerator;
use Engelsystem\Renderer\Twig\Extensions\Url;
use PHPUnit\Framework\MockObject\MockObject;

class UrlTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Url::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Url::getFunctions
     */
    public function testGetGlobals(): void
    {
        /** @var UrlGenerator|MockObject $urlGenerator */
        $urlGenerator = $this->createMock(UrlGenerator::class);

        $extension = new Url($urlGenerator);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('url', [$extension, 'getUrl'], $functions);
    }

    /**
     * @return string[][]
     */
    public function getUrls(): array
    {
        return [
            ['/', '/', 'http://foo.bar/'],
            ['/foo', '/foo', 'http://foo.bar/foo'],
            ['foo_bar', 'foo-bar', 'http://foo.bar/foo-bar'],
            ['dolor', 'dolor', 'http://foo.bar/dolor?lorem_ipsum=dolor', ['lorem_ipsum' => 'dolor']],
        ];
    }

    /**
     * @dataProvider getUrls
     *
     * @covers \Engelsystem\Renderer\Twig\Extensions\Url::getUrl
     */
    public function testGetUrl(string $url, string $urlTo, string $return, array $parameters = []): void
    {
        /** @var UrlGenerator|MockObject $urlGenerator */
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
