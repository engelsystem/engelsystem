<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Application;
use Engelsystem\Container\Container;
use Engelsystem\Http\LegacyUrlGenerator;
use Engelsystem\Http\Request;
use Engelsystem\Http\UrlGeneratorInterface;
use PHPUnit\Framework\TestCase;

class LegacyUrlGeneratorTest extends TestCase
{
    public function provideLinksTo()
    {
        return [
            ['/', 'http://foo.bar/index.php', [], 'http://foo.bar/'],
            ['/foo-path', 'http://foo.bar/index.php/index.php', [], 'http://foo.bar/index.php?p=foo_path'],
            ['/foo', 'http://foo.bar/index.php/index.php', [], 'http://foo.bar/index.php?p=foo'],
            ['foo', 'http://foo.bar/index.php', ['test' => 'abc'], 'http://foo.bar/index.php?p=foo&test=abc'],
        ];
    }

    /**
     * @dataProvider provideLinksTo
     * @covers       \Engelsystem\Http\LegacyUrlGenerator::to
     *
     * @param string   $urlToPath
     * @param string   $willReturn
     * @param string[] $arguments
     * @param string   $expectedUrl
     */
    public function testTo($urlToPath, $willReturn, $arguments, $expectedUrl)
    {
        $app = new Container();
        Application::setInstance($app);

        $request = $this->getMockBuilder(Request::class)
            ->getMock();

        $request->expects($this->once())
            ->method('getUriForPath')
            ->with('/index.php')
            ->willReturn($willReturn);

        $app->instance('request', $request);

        $urlGenerator = new LegacyUrlGenerator();
        $this->assertInstanceOf(UrlGeneratorInterface::class, $urlGenerator);

        $url = $urlGenerator->to($urlToPath, $arguments);
        $this->assertEquals($expectedUrl, $url);
    }
}
