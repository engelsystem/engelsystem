<?php

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
    /**
     * @return array
     */
    public function provideRegister()
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
     * @covers       \Engelsystem\Http\RequestServiceProvider::register()
     *
     * @param string|array $configuredProxies
     * @param array        $trustedProxies
     */
    public function testRegister($configuredProxies, $trustedProxies)
    {
        /** @var Config|MockObject $config */
        $config = $this->getMockBuilder(Config::class)->getMock();
        /** @var Request|MockObject $request */
        $request = $this->getMockBuilder(Request::class)->getMock();

        $app = $this->getApp(['call', 'get', 'instance']);

        $this->setExpects($app, 'call', [[Request::class, 'createFromGlobals']], $request);
        $this->setExpects($app, 'get', ['config'], $config);
        $this->setExpects($config, 'get', ['trusted_proxies'], $configuredProxies);

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
            ->setMethods(['setTrustedProxies'])
            ->getMock();
        $serviceProvider->expects($this->once())
            ->method('setTrustedProxies')
            ->with($request, $trustedProxies);
        $serviceProvider->register();
    }
}
