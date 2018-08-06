<?php

namespace Engelsystem\Test\Unit\Routing;

use Engelsystem\Application;
use Engelsystem\Container\Container;
use Engelsystem\Http\Request;
use Engelsystem\Routing\UrlGenerator;
use Engelsystem\Routing\UrlGeneratorInterface;
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
     * @covers       \Engelsystem\Routing\UrlGenerator::linkTo
     *
     * @param string   $path
     * @param string   $willReturn
     * @param string   $urlToPath
     * @param string[] $arguments
     * @param string   $expectedUrl
     */
    public function testLinkTo($urlToPath, $path, $willReturn, $arguments, $expectedUrl)
    {
        $app = new Container();
        Application::setInstance($app);

        $request = $this->getMockBuilder(Request::class)
            ->getMock();

        $request->expects($this->once())
            ->method('getUriForPath')
            ->with($path)
            ->willReturn($willReturn);

        $app->instance('request', $request);

        $urlGenerator = new UrlGenerator();
        $this->assertInstanceOf(UrlGeneratorInterface::class, $urlGenerator);

        $url = $urlGenerator->linkTo($urlToPath, $arguments);
        $this->assertEquals($expectedUrl, $url);
    }
}
