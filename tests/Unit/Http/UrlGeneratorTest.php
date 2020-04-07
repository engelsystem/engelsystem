<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Config\Config;
use Engelsystem\Http\Request;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Test\Unit\TestCase;

class UrlGeneratorTest extends TestCase
{
    /**
     * @return array
     */
    public function provideLinksTo(): array
    {
        return [
            ['/foo/path', '/foo/path', 'http://foo.bar/foo/path', [], 'http://foo.bar/foo/path'],
            ['foo', '/foo', 'https://foo.bar/foo', [], 'https://foo.bar/foo'],
            ['foo', '/foo', 'http://f.b/foo', ['test' => 'abc', 'bla' => 'foo'], 'http://f.b/foo?test=abc&bla=foo'],
        ];
    }

    /**
     * @dataProvider provideLinksTo
     * @covers       \Engelsystem\Http\UrlGenerator::to
     * @covers       \Engelsystem\Http\UrlGenerator::generateUrl
     *
     * @param string   $path
     * @param string   $willReturn
     * @param string   $urlToPath
     * @param string[] $arguments
     * @param string   $expectedUrl
     */
    public function testTo($urlToPath, $path, $willReturn, $arguments, $expectedUrl)
    {
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

    /**
     * @covers \Engelsystem\Http\UrlGenerator::to
     */
    public function testToWithValidUrl()
    {
        $url = new UrlGenerator();
        $this->app->instance('config', new Config());

        $this->assertEquals('https://foo.bar/batz', $url->to('https://foo.bar/batz'));
        $this->assertEquals('https://some.url?lorem=ipsum', $url->to('https://some.url', ['lorem' => 'ipsum']));
        $this->assertEquals('mailto:foo@bar.batz', $url->to('mailto:foo@bar.batz'));
        $this->assertEquals('#some-anchor', $url->to('#some-anchor'));
    }

    /**
     * @covers \Engelsystem\Http\UrlGenerator::to
     * @covers \Engelsystem\Http\UrlGenerator::generateUrl
     */
    public function testToWithApplicationURL()
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

    /**
     * @covers \Engelsystem\Http\UrlGenerator::isValidUrl
     */
    public function testIsValidUrl()
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
