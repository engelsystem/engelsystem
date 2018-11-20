<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Request;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyFile;
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
     * @covers \Engelsystem\Http\Request::hasPostData
     */
    public function testHasPostData()
    {
        $request = new Request([
            'foo' => 'bar',
        ], [
            'lorem' => 'ipsum',
        ]);

        $this->assertTrue($request->has('foo'));
        $this->assertFalse($request->hasPostData('foo'));

        $this->assertTrue($request->has('lorem'));
        $this->assertTrue($request->hasPostData('lorem'));
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

    /**
     * @covers \Engelsystem\Http\Request::getServerParams
     */
    public function testGetServerParams()
    {
        $server = ['foo' => 'bar'];
        $request = new Request([], [], [], [], [], $server);

        $this->assertEquals($server, $request->getServerParams());
    }

    /**
     * @covers \Engelsystem\Http\Request::getCookieParams
     */
    public function testGetCookieParams()
    {
        $cookies = ['session' => 'LoremIpsumDolorSit'];
        $request = new Request([], [], [], $cookies);

        $this->assertEquals($cookies, $request->getCookieParams());
    }

    /**
     * @covers \Engelsystem\Http\Request::withCookieParams
     */
    public function testWithCookieParams()
    {
        $cookies = ['lor' => 'em'];
        $request = new Request();

        $new = $request->withCookieParams($cookies);

        $this->assertNotEquals($request, $new);
        $this->assertEquals($cookies, $new->getCookieParams());
    }

    /**
     * @covers \Engelsystem\Http\Request::getQueryParams
     */
    public function testGetQueryParams()
    {
        $params = ['foo' => 'baz'];
        $request = new Request($params);

        $this->assertEquals($params, $request->getQueryParams());
    }

    /**
     * @covers \Engelsystem\Http\Request::withQueryParams
     */
    public function testWithQueryParams()
    {
        $params = ['test' => 'ing'];
        $request = new Request();

        $new = $request->withQueryParams($params);

        $this->assertNotEquals($request, $new);
        $this->assertEquals($params, $new->getQueryParams());
    }

    /**
     * @covers \Engelsystem\Http\Request::getUploadedFiles
     */
    public function testGetUploadedFiles()
    {
        $filename = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($filename, 'LoremIpsum!');
        $files = [new SymfonyFile($filename, 'foo.html', 'text/html', 11)];
        $request = new Request([], [], [], [], $files);

        $uploadedFiles = $request->getUploadedFiles();
        $this->assertNotEmpty($uploadedFiles);

        /** @var UploadedFileInterface $file */
        $file = $uploadedFiles[0];
        $this->assertInstanceOf(UploadedFileInterface::class, $file);
        $this->assertEquals('foo.html', $file->getClientFilename());
        $this->assertEquals('text/html', $file->getClientMediaType());
        $this->assertEquals(11, $file->getSize());
    }

    /**
     * @covers \Engelsystem\Http\Request::withUploadedFiles
     */
    public function testWithUploadedFiles()
    {
        $filename = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($filename, 'LoremIpsum!');
        $file = new \Zend\Diactoros\UploadedFile($filename, 11, UPLOAD_ERR_OK, 'test.txt', 'text/plain');

        $request = new Request();
        $new = $request->withUploadedFiles([$file]);
        $uploadedFiles = $new->getUploadedFiles();
        $this->assertNotEquals($request, $new);
        $this->assertNotEmpty($uploadedFiles);

        /** @var UploadedFileInterface $file */
        $file = $uploadedFiles[0];
        $this->assertEquals('test.txt', $file->getClientFilename());
        $this->assertEquals('text/plain', $file->getClientMediaType());
        $this->assertEquals(11, $file->getSize());
    }

    /**
     * @covers \Engelsystem\Http\Request::getParsedBody
     */
    public function testGetParsedBody()
    {
        $body = ['foo' => 'lorem'];
        $request = new Request();
        $request->request->add($body);

        $this->assertEquals($body, $request->getParsedBody());
    }

    /**
     * @covers \Engelsystem\Http\Request::withParsedBody
     */
    public function testWithParsedBody()
    {
        $data = ['test' => 'er'];
        $request = new Request();

        $new = $request->withParsedBody($data);

        $this->assertNotEquals($request, $new);
        $this->assertEquals($data, $new->getParsedBody());
    }

    /**
     * @covers \Engelsystem\Http\Request::getAttributes
     */
    public function testGetAttributes()
    {
        $attributes = ['foo' => 'lorem', 'ipsum' => 'dolor'];
        $request = new Request([], [], $attributes);

        $this->assertEquals($attributes, $request->getAttributes());
    }

    /**
     * @covers \Engelsystem\Http\Request::getAttribute
     */
    public function testGetAttribute()
    {
        $attributes = ['foo' => 'lorem', 'ipsum' => 'dolor'];
        $request = new Request([], [], $attributes);

        $this->assertEquals($attributes['ipsum'], $request->getAttribute('ipsum'));
        $this->assertEquals(null, $request->getAttribute('dolor'));
        $this->assertEquals(1234, $request->getAttribute('test', 1234));
    }

    /**
     * @covers \Engelsystem\Http\Request::withAttribute
     */
    public function testWithAttribute()
    {
        $request = new Request();

        $new = $request->withAttribute('lorem', 'ipsum');

        $this->assertNotEquals($request, $new);
        $this->assertEquals('ipsum', $new->getAttribute('lorem'));
    }

    /**
     * @covers \Engelsystem\Http\Request::withoutAttribute
     */
    public function testWithoutAttribute()
    {
        $attributes = ['foo' => 'lorem', 'ipsum' => 'dolor'];
        $request = new Request([], [], $attributes);

        $new = $request->withoutAttribute('ipsum');

        $this->assertNotEquals($request, $new);
        $this->assertEquals(['foo' => 'lorem'], $new->getAttributes());
    }
}
