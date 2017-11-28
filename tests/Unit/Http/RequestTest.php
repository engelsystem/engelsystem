<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Request;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class RequestTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Request::postData
     */
    public function testPostData()
    {
        $request = new Request(
            ['foo' => 'I\'m a test!'],
            ['foo' => 'bar']
        );

        $this->assertEquals('bar', $request->postData('foo'));
        $this->assertEquals('LoremIpsum', $request->postData('test-key', 'LoremIpsum'));
    }

    /**
     * @covers \Engelsystem\Http\Request::input
     */
    public function testInput()
    {
        $request = new Request(
            ['foo' => 'I\'m a test!'],
            ['foo' => 'bar']
        );

        $this->assertEquals('I\'m a test!', $request->input('foo'));
        $this->assertEquals('LoremIpsum', $request->input('test-key', 'LoremIpsum'));
    }

    /**
     * @covers \Engelsystem\Http\Request::has
     */
    public function testHas()
    {
        $request = new Request([
            'foo' => 'I\'m a test!',
            'bar' => '',
        ]);

        $this->assertTrue($request->has('foo'));
        $this->assertTrue($request->has('bar'));
        $this->assertFalse($request->has('baz'));
    }

    /**
     * @covers \Engelsystem\Http\Request::path
     */
    public function testPath()
    {
        /** @var MockObject|Request $request */
        $request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['getPathInfo'])
            ->getMock();

        $request
            ->expects($this->atLeastOnce())
            ->method('getPathInfo')
            ->willReturnOnConsecutiveCalls(
                '/foo',
                '/'
            );

        $this->assertEquals('foo', $request->path());
        $this->assertEquals('/', $request->path());
    }

    /**
     * @covers \Engelsystem\Http\Request::url
     */
    public function testUrl()
    {
        /** @var MockObject|Request $request */
        $request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['getUri'])
            ->getMock();

        $request
            ->expects($this->atLeastOnce())
            ->method('getUri')
            ->willReturnOnConsecutiveCalls(
                'http://foo.bar/bla/foo/',
                'https://lorem.ipsum/dolor/sit?amet=consetetur&sadipscing=elitr'
            );

        $this->assertEquals('http://foo.bar/bla/foo', $request->url());
        $this->assertEquals('https://lorem.ipsum/dolor/sit', $request->url());
    }
}
