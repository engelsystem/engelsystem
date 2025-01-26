<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Http\Request;
use Engelsystem\Http\RequestServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class RequestServiceProviderTest extends ServiceProviderTest
{
    public function provideRegister(): array
    {
        return [
            ['', []],
            [[], []],
            ['192.168.10.99', ['192.168.10.99']],
            [' 234.234.234.234 ', ['234.234.234.234']],
            ['123.234.123.234,10.0.0.0/8', ['123.234.123.234', '10.0.0.0/8']],
            ['123.123.234.234 , ' . PHP_EOL . ' 11.22.33.44/22 ', ['123.123.234.234', '11.22.33.44/22']],
            [['10.100.20.0/24'], ['10.100.20.0/24']],
        ];
    }

    /**
     * @dataProvider provideRegister
     * @covers       \Engelsystem\Http\RequestServiceProvider::register
     */
    public function testRegister(string|array $configuredProxies, array $trustedProxies): void
    {
        $config = new Config([
            'trusted_proxies' => $configuredProxies,
        ]);
        /** @var Request|MockObject $request */
        $request = $this->getMockBuilder(Request::class)->getMock();

        $app = $this->getApp(['call', 'get', 'instance']);

        $this->setExpects($app, 'call', [[Request::class, 'createFromGlobals']], $request);
        $this->setExpects($app, 'get', ['config'], $config);

        $app->expects($this->exactly(3))
            ->method('instance')
            ->withConsecutive(
                [Request::class, $request],
                [SymfonyRequest::class, $request],
                ['request', $request]
            );

        /** @var ServiceProvider|MockObject $serviceProvider */
        $serviceProvider = $this->getMockBuilder(RequestServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['setTrustedProxies'])
            ->getMock();
        $this->setExpects($serviceProvider, 'setTrustedProxies', [$request, $trustedProxies]);
        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Http\RequestServiceProvider::register
     */
    public function testRegisterRewritingPrefix(): void
    {
        $config = new Config([
            'url' => 'https://some.app/subpath',
        ]);
        $this->app->instance('config', $config);
        $request = new Request();

        /** @var ServiceProvider|MockObject $serviceProvider */
        $serviceProvider = $this->getMockBuilder(RequestServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['createRequestWithoutPrefix'])
            ->getMock();
        $this->setExpects($serviceProvider, 'createRequestWithoutPrefix', null, $request);

        $serviceProvider->register();
    }

    /**
     * Provide test data: [requested uri; expected rewrite, configured app url]
     *
     * @return string[][]
     */
    public function provideRequestPathPrefix(): array
    {
        return [
            ['/', '/'],
            ['/sub', '/sub'],
            ['/subpath2', '/subpath2'],
            ['/subpath2/test', '/subpath2/test'],
            ['/subpath', '/'],
            ['/subpath/', '/'],
            ['/subpath/test', '/test'],
            ['/subpath/foo/bar', '/foo/bar'],
            ['/path/foo/bar', '/foo/bar', 'https://some.app/path/'],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\RequestServiceProvider::createRequestWithoutPrefix
     * @dataProvider provideRequestPathPrefix
     */
    public function testCreateRequestWithoutPrefix(string $requestUri, string $expected, ?string $url = null): void
    {
        $_SERVER['REQUEST_URI'] = $requestUri;
        $config = new Config([
            'url' => $url ?: 'https://some.app/subpath',
        ]);
        $this->app->instance('config', $config);
        $serviceProvider = new RequestServiceProvider($this->app);
        $serviceProvider->register();

        /** @var Request $request */
        $request = $this->app->get('request');
        $this->assertEquals($expected, $request->getPathInfo());
    }
}
