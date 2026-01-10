<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Config\Config;
use Engelsystem\Http\Request;
use Engelsystem\Http\RequestServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

#[CoversMethod(RequestServiceProvider::class, 'register')]
#[CoversMethod(RequestServiceProvider::class, 'createRequestWithoutPrefix')]
class RequestServiceProviderTest extends ServiceProviderTestCase
{
    public static function provideRegister(): array
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

    #[DataProvider('provideRegister')]
    public function testRegister(string|array $configuredProxies, array $trustedProxies): void
    {
        $config = new Config([
            'trusted_proxies' => $configuredProxies,
        ]);
        $request = $this->getStubBuilder(Request::class)->getStub();

        $app = $this->getAppMock(['call', 'get', 'instance']);

        $this->setExpects($app, 'call', [[Request::class, 'createFromGlobals']], $request);
        $this->setExpects($app, 'get', ['config'], $config);

        $matcher = $this->exactly(3);
        $app->expects($matcher)
            ->method('instance')->willReturnCallback(function (...$parameters) use ($matcher, $request): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(Request::class, $parameters[0]);
                    $this->assertSame($request, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame(SymfonyRequest::class, $parameters[0]);
                    $this->assertSame($request, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('request', $parameters[0]);
                    $this->assertSame($request, $parameters[1]);
                }
            });

        $serviceProvider = $this->getMockBuilder(RequestServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['setTrustedProxies'])
            ->getMock();
        $this->setExpects($serviceProvider, 'setTrustedProxies', [$request, $trustedProxies]);
        $serviceProvider->register();
    }

    public function testRegisterRewritingPrefix(): void
    {
        $config = new Config([
            'url' => 'https://some.app/subpath',
        ]);
        $this->app->instance('config', $config);
        $request = new Request();

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
    public static function provideRequestPathPrefix(): array
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

    #[DataProvider('provideRequestPathPrefix')]
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
