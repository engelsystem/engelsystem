<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Psr7ServiceProvider;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class Psr7ServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\Psr7ServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var MockObject|DiactorosFactory $psr7Factory */
        $psr7Factory = $this->createMock(DiactorosFactory::class);
        /** @var MockObject|Request $request */
        $request = $this->createMock(Request::class);
        /** @var MockObject|Response $response */
        $response = $this->createMock(Response::class);
        /** @var MockObject|RequestInterface $psr7request */
        $psr7request = $this->createMock(Request::class);
        /** @var MockObject|ResponseInterface $psr7response */
        $psr7response = $this->createMock(Response::class);

        $app = $this->getApp(['make', 'instance', 'get', 'bind']);
        $this->setExpects($app, 'make', [DiactorosFactory::class], $psr7Factory);

        $app->expects($this->atLeastOnce())
            ->method('get')
            ->withConsecutive(['request'], ['response'])
            ->willReturnOnConsecutiveCalls($request, $response);
        $app->expects($this->atLeastOnce())
            ->method('instance')
            ->withConsecutive(
                ['psr7.factory', $psr7Factory],
                ['psr7.request', $psr7request],
                ['psr7.response', $psr7response]
            );
        $app->expects($this->atLeastOnce())
            ->method('bind')
            ->withConsecutive(
                [RequestInterface::class, 'psr7.request'],
                [ResponseInterface::class, 'psr7.response']
            );

        $serviceProvider = new Psr7ServiceProvider($app);
        $serviceProvider->register();
    }
}
