<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Config\Config;
use Engelsystem\Http\Request;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversMethod(UrlGenerator::class, 'to')]
#[CoversMethod(UrlGenerator::class, 'generateUrl')]
#[CoversMethod(UrlGenerator::class, 'isValidUrl')]
class UrlGeneratorTest extends TestCase
{
    public static function provideLinksTo(): array
    {
        return [
            ['/foo/path', '/foo/path', 'https://foo.bar/foo/path', [], 'https://foo.bar/foo/path'],
            ['foo', '/foo', 'https://foo.bar/foo', [], 'https://foo.bar/foo'],
            ['foo', '/foo', 'https://f.b/foo', ['test' => 'abc', 'bla' => 'foo'], 'https://f.b/foo?test=abc&bla=foo'],
        ];
    }

    /**
     *
     * @param string[] $arguments
     */
    #[DataProvider('provideLinksTo')]
    public function testTo(
        string $urlToPath,
        string $path,
        string $willReturn,
        array $arguments,
        string $expectedUrl
    ): void {
        $request = $this->getMockBuilder(Request::class)
            ->getMock();
        $request->expects($this->once())
            ->method('getUriForPath')
            ->with($path)
            ->willReturn($willReturn);
        $this->app->instance('request', $request);
        $this->app->instance('config', new Config());

        $urlGenerator = new UrlGenerator();

        $url = $urlGenerator->to($urlToPath, $arguments);
        $this->assertEquals($expectedUrl, $url);
    }

    public function testToWithValidUrl(): void
    {
        $url = new UrlGenerator();
        $this->app->instance('config', new Config());

        $this->assertEquals('https://foo.bar/batz', $url->to('https://foo.bar/batz'));
        $this->assertEquals('https://some.url?lorem=ipsum', $url->to('https://some.url', ['lorem' => 'ipsum']));
        $this->assertEquals('mailto:foo@bar.batz', $url->to('mailto:foo@bar.batz'));
        $this->assertEquals('#some-anchor', $url->to('#some-anchor'));
    }

    public function testToWithApplicationURL(): void
    {
        $this->app->instance('config', new Config(['url' => 'https://foo.bar/base/']));

        $url = new UrlGenerator();

        $this->assertEquals('https://foo.bar/base/test', $url->to('test'));
        $this->assertEquals('https://foo.bar/base/test', $url->to('/test'));
        $this->assertEquals('https://foo.bar/base/lorem?ipsum=dolor', $url->to('/lorem', ['ipsum' => 'dolor']));

        $this->app->instance('config', new Config(['url' => 'https://foo.bar/base']));
        $this->assertEquals('https://foo.bar/base/test', $url->to('test'));
        $this->assertEquals('https://foo.bar/base/test', $url->to('/test'));
    }

    public function testIsValidUrl(): void
    {
        $url = new UrlGenerator();

        $this->assertTrue($url->isValidUrl('https://foo.bar'));
        $this->assertTrue($url->isValidUrl('#foo-bar'));
        $this->assertTrue($url->isValidUrl('tel:+123456'));
        $this->assertTrue($url->isValidUrl('ftp://foo@bar.batz'));

        $this->assertFalse($url->isValidUrl('foo/bar'));
        $this->assertFalse($url->isValidUrl('foo/uff://bar'));
        $this->assertFalse($url->isValidUrl('lorem/ipsum#dolor'));
    }
}
