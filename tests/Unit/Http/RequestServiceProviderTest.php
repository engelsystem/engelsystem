<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Request;
use Engelsystem\Http\RequestServiceProvider;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class RequestServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Http\RequestServiceProvider::register()
     */
    public function testRegister()
    {
        /** @var MockObject|Request $request */
        $request = $this->getMockBuilder(Request::class)
            ->getMock();

        $app = $this->getApp(['call', 'instance']);

        $this->setExpects($app, 'call', [[Request::class, 'createFromGlobals']], $request);
        $this->setExpects($app, 'instance', ['request', $request]);

        $serviceProvider = new RequestServiceProvider($app);
        $serviceProvider->register();
    }
}
