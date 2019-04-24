<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Response;
use Engelsystem\Http\ResponseServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\ResponseServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var Response|MockObject $response */
        $response = $this->getMockBuilder(Response::class)
            ->getMock();

        $app = $this->getApp();

        $this->setExpects($app, 'make', [Response::class], $response);
        $app->expects($this->exactly(3))
            ->method('instance')
            ->withConsecutive(
                [Response::class, $response],
                [SymfonyResponse::class, $response],
                ['response', $response]
            );

        $serviceProvider = new ResponseServiceProvider($app);
        $serviceProvider->register();
    }
}
