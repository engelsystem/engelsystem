<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use PHPUnit\Framework\TestCase;

class RedirectorTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Redirector::__construct
     * @covers \Engelsystem\Http\Redirector::to
     */
    public function testTo()
    {
        $request = new Request();
        $response = new Response();
        $redirector = new Redirector($request, $response);

        $return = $redirector->to('/test');
        $this->assertEquals(['/test'], $return->getHeader('location'));
        $this->assertEquals(302, $return->getStatusCode());

        $return = $redirector->to('/foo', 303, ['test' => 'data']);
        $this->assertEquals(['/foo'], $return->getHeader('location'));
        $this->assertEquals(303, $return->getStatusCode());
        $this->assertEquals(['data'], $return->getHeader('test'));
    }

    /**
     * @covers \Engelsystem\Http\Redirector::back
     * @covers \Engelsystem\Http\Redirector::getPreviousUrl
     */
    public function testBack()
    {
        $request = new Request();
        $response = new Response();
        $redirector = new Redirector($request, $response);

        $return = $redirector->back();
        $this->assertEquals(['/'], $return->getHeader('location'));
        $this->assertEquals(302, $return->getStatusCode());

        $request = $request->withHeader('referer', '/old-page');
        $redirector = new Redirector($request, $response);
        $return = $redirector->back(303, ['foo' => 'bar']);
        $this->assertEquals(303, $return->getStatusCode());
        $this->assertEquals(['/old-page'], $return->getHeader('location'));
        $this->assertEquals(['bar'], $return->getHeader('foo'));
    }
}
