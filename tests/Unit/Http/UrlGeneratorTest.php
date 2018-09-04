<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Application;
use Engelsystem\Container\Container;
use Engelsystem\Http\Request;
use Engelsystem\Http\UrlGenerator;
use PHPUnit\Framework\TestCase;

class UrlGeneratorTest extends TestCase
{
    public function provideLinksTo()
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
     *
     * @param string   $path
     * @param string   $willReturn
     * @param string   $urlToPath
     * @param string[] $arguments
     * @param string   $expectedUrl
     */
    public function testTo($urlToPath, $path, $willReturn, $arguments, $expectedUrl)
    {
        $app = new Container();
        $urlGenerator = new UrlGenerator();
        Application::setInstance($app);

        $request = $this->getMockBuilder(Request::class)
            ->getMock();

        $request->expects($this->once())
            ->method('getUriForPath')
            ->with($path)
            ->willReturn($willReturn);

        $app->instance('request', $request);

        $url = $urlGenerator->to($urlToPath, $arguments);
        $this->assertEquals($expectedUrl, $url);
    }
}
