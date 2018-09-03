<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Response;
use Engelsystem\Http\ResponseServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ResponseServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\ResponseServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var MockObject|Response $response */
        $response = $this->getMockBuilder(Response::class)
            ->getMock();

        $app = $this->getApp();

        $this->setExpects($app, 'make', [Response::class], $response);
        $this->setExpects($app, 'instance', ['response', $response]);

        $serviceProvider = new ResponseServiceProvider($app);
        $serviceProvider->register();
    }
}
