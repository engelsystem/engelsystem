<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Request;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class RequestTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Request
     */
    public function testCreate()
    {
        $response = new Request();
        $this->assertInstanceOf(SymfonyRequest::class, $response);
        $this->assertInstanceOf(RequestInterface::class, $response);
    }

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

    /**
     * @covers \Engelsystem\Http\Request::getRequestTarget
     */
    public function testGetRequestTarget()
    {
        /** @var Request|MockObject $request */
        $request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['getQueryString', 'path'])
            ->getMock();

        $request->expects($this->exactly(2))
            ->method('getQueryString')
            ->willReturnOnConsecutiveCalls(null, 'foo=bar&lorem=ipsum');
        $request->expects($this->exactly(2))
            ->method('path')
            ->willReturn('foo/bar');

        $this->assertEquals('/foo/bar', $request->getRequestTarget());
        $this->assertEquals('/foo/bar?foo=bar&lorem=ipsum', $request->getRequestTarget());
    }

    /**
     * @covers \Engelsystem\Http\Request::withRequestTarget
     */
    public function testWithRequestTarget()
    {
        $request = new Request();
        foreach (
            [
                '*',
                '/foo/bar',
                'https://lorem.ipsum/test?lor=em'
            ] as $target
        ) {
            $new = $request->withRequestTarget($target);
            $this->assertNotEquals($request, $new);
        }
    }

    /**
     * @covers \Engelsystem\Http\Request::withMethod
     */
    public function testWithMethod()
    {
        $request = new Request();

        $new = $request->withMethod('PUT');

        $this->assertNotEquals($request, $new);
        $this->assertEquals('PUT', $new->getMethod());
    }

    /**
     * @covers \Engelsystem\Http\Request::withUri
     */
    public function testWithUri()
    {
        /** @var UriInterface|MockObject $uri */
        $uri = $this->getMockForAbstractClass(UriInterface::class);

        $uri->expects($this->atLeastOnce())
            ->method('__toString')
            ->willReturn('http://foo.bar/bla?foo=bar');

        $request = Request::create('http://lor.em/');

        $new = $request->withUri($uri);
        $this->assertNotEquals($request, $new);
        $this->assertEquals('http://foo.bar/bla?foo=bar', (string)$new->getUri());

        $new = $request->withUri($uri, true);
        $this->assertEquals('http://lor.em/bla?foo=bar', (string)$new->getUri());
    }

    /**
     * @covers \Engelsystem\Http\Request::getUri
     */
    public function testGetUri()
    {
        $request = Request::create('http://lor.em/test?bla=foo');

        $uri = $request->getUri();
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertEquals('http://lor.em/test?bla=foo', (string)$uri);
    }
}
