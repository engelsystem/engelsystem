<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Request;
use Nyholm\Psr7\UploadedFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

#[CoversClass(Request::class)]
#[CoversMethod(Request::class, 'postData')]
#[CoversMethod(Request::class, 'input')]
#[CoversMethod(Request::class, 'has')]
#[CoversMethod(Request::class, 'get')]
#[CoversMethod(Request::class, 'hasPostData')]
#[CoversMethod(Request::class, 'path')]
#[CoversMethod(Request::class, 'url')]
#[CoversMethod(Request::class, 'getRequestTarget')]
#[CoversMethod(Request::class, 'withRequestTarget')]
#[CoversMethod(Request::class, 'withMethod')]
#[CoversMethod(Request::class, 'withUri')]
#[CoversMethod(Request::class, 'getServerParams')]
#[CoversMethod(Request::class, 'getCookieParams')]
#[CoversMethod(Request::class, 'withCookieParams')]
#[CoversMethod(Request::class, 'getQueryParams')]
#[CoversMethod(Request::class, 'withQueryParams')]
#[CoversMethod(Request::class, 'getUploadedFiles')]
#[CoversMethod(Request::class, 'withUploadedFiles')]
#[CoversMethod(Request::class, 'getParsedBody')]
#[CoversMethod(Request::class, 'withParsedBody')]
#[CoversMethod(Request::class, 'getAttributes')]
#[CoversMethod(Request::class, 'getAttribute')]
#[CoversMethod(Request::class, 'withAttribute')]
#[CoversMethod(Request::class, 'withoutAttribute')]
class RequestTest extends TestCase
{
    public function testCreate(): void
    {
        $response = new Request();
        $this->assertInstanceOf(SymfonyRequest::class, $response);
        $this->assertInstanceOf(RequestInterface::class, $response);
    }

    public function testPostData(): void
    {
        $request = new Request(
            ['foo' => 'I\'m a test!'],
            ['foo' => 'bar']
        );

        $this->assertEquals('bar', $request->postData('foo'));
        $this->assertEquals('LoremIpsum', $request->postData('test-key', 'LoremIpsum'));
    }

    public function testInput(): void
    {
        $request = new Request(
            ['foo' => 'I\'m a test!'],
            ['foo' => 'bar']
        );

        $this->assertEquals('I\'m a test!', $request->input('foo'));
        $this->assertEquals('LoremIpsum', $request->input('test-key', 'LoremIpsum'));
    }

    public function testHas(): void
    {
        $request = new Request([
            'foo' => 'I\'m a test!',
            'bar' => '',
        ]);

        $this->assertTrue($request->has('foo'));
        $this->assertTrue($request->has('bar'));
        $this->assertFalse($request->has('baz'));
    }

    public function testGet(): void
    {
        $request = new Request([
            // Query / GET
            'a' => 'From query',
            'g' => 'From query',
        ], [
            // Request / POST
            'a' => 'From request',
            'g' => 'From request',
            'p' => 'From request',
        ], [
            // Attributes
            'a' => 'From attributes',
        ]);

        $this->assertEquals('From attributes', $request->get('a'));
        $this->assertEquals('From query', $request->get('g'));
        $this->assertEquals('From request', $request->get('p'));
        $this->assertEquals('default value', $request->get('not-existing', 'default value'));
    }

    public function testHasPostData(): void
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

    public function testPath(): void
    {
        $request = $this
            ->getMockBuilder(Request::class)
            ->onlyMethods(['getPathInfo'])
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

    public function testUrl(): void
    {
        $request = $this
            ->getMockBuilder(Request::class)
            ->onlyMethods(['getUri'])
            ->getMock();

        $request
            ->expects($this->atLeastOnce())
            ->method('getUri')
            ->willReturnOnConsecutiveCalls(
                'https://foo.bar/bla/foo/',
                'https://lorem.ipsum/dolor/sit?amet=consetetur&sadipscing=elitr'
            );

        $this->assertEquals('https://foo.bar/bla/foo', $request->url());
        $this->assertEquals('https://lorem.ipsum/dolor/sit', $request->url());
    }

    public function testGetRequestTarget(): void
    {
        $request = $this
            ->getMockBuilder(Request::class)
            ->onlyMethods(['getQueryString', 'path'])
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

    public function testWithRequestTarget(): void
    {
        $request = new Request();
        foreach (
            [
                '*',
                '/foo/bar',
                'https://lorem.ipsum/test?lor=em',
            ] as $target
        ) {
            $new = $request->withRequestTarget($target);
            $this->assertNotEquals($request, $new);
        }
    }

    public function testWithMethod(): void
    {
        $request = new Request();

        $new = $request->withMethod('PUT');

        $this->assertNotEquals($request, $new);
        $this->assertEquals('PUT', $new->getMethod());
    }

    public function testWithUri(): void
    {
        $uri = $this->getMockBuilder(UriInterface::class)->getMock();

        $uri->expects($this->atLeastOnce())
            ->method('__toString')
            ->willReturn('https://foo.bar/bla?foo=bar');

        $request = Request::create('https://lor.em/');

        $new = $request->withUri($uri);
        $this->assertNotEquals($request, $new);
        $this->assertEquals('https://foo.bar/bla?foo=bar', $new->getUri());

        $new = $request->withUri($uri, true);
        $this->assertEquals('https://lor.em/bla?foo=bar', $new->getUri());
    }

    public function testGetServerParams(): void
    {
        $server = ['foo' => 'bar'];
        $request = new Request([], [], [], [], [], $server);

        $this->assertEquals($server, $request->getServerParams());
    }

    public function testGetCookieParams(): void
    {
        $cookies = ['session' => 'LoremIpsumDolorSit'];
        $request = new Request([], [], [], $cookies);

        $this->assertEquals($cookies, $request->getCookieParams());
    }

    public function testWithCookieParams(): void
    {
        $cookies = ['lor' => 'em'];
        $request = new Request();

        $new = $request->withCookieParams($cookies);

        $this->assertNotEquals($request, $new);
        $this->assertEquals($cookies, $new->getCookieParams());
    }

    public function testGetQueryParams(): void
    {
        $params = ['foo' => 'baz'];
        $request = new Request($params);

        $this->assertEquals($params, $request->getQueryParams());
    }

    public function testWithQueryParams(): void
    {
        $params = ['test' => 'ing'];
        $request = new Request();

        $new = $request->withQueryParams($params);

        $this->assertNotEquals($request, $new);
        $this->assertEquals($params, $new->getQueryParams());
    }

    public function testGetUploadedFiles(): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($filename, 'LoremIpsum!');
        $files = [new SymfonyFile($filename, 'foo.txt', 'text/plain', UPLOAD_ERR_PARTIAL)];
        $request = new Request([], [], [], [], $files);

        $uploadedFiles = $request->getUploadedFiles();
        $this->assertNotEmpty($uploadedFiles);

        /** @var UploadedFileInterface $file */
        $file = $uploadedFiles[0];
        $this->assertInstanceOf(UploadedFileInterface::class, $file);
        $this->assertEquals('foo.txt', $file->getClientFilename());
        $this->assertEquals('text/plain', $file->getClientMediaType());
        $this->assertEquals(11, $file->getSize());
        $this->assertEquals(UPLOAD_ERR_PARTIAL, $file->getError());
    }

    public function testWithUploadedFiles(): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($filename, 'LoremIpsum!');
        $file = new UploadedFile($filename, 11, UPLOAD_ERR_OK, 'test.txt', 'text/plain');

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

    public function testGetParsedBody(): void
    {
        $body = ['foo' => 'lorem'];
        $request = new Request();
        $request->request->add($body);

        $this->assertEquals($body, $request->getParsedBody());
    }

    public function testWithParsedBody(): void
    {
        $data = ['test' => 'er'];
        $request = new Request();

        $new = $request->withParsedBody($data);

        $this->assertNotEquals($request, $new);
        $this->assertEquals($data, $new->getParsedBody());
    }

    public function testGetAttributes(): void
    {
        $attributes = ['foo' => 'lorem', 'ipsum' => 'dolor'];
        $request = new Request([], [], $attributes);

        $this->assertEquals($attributes, $request->getAttributes());
    }

    public function testGetAttribute(): void
    {
        $attributes = ['foo' => 'lorem', 'ipsum' => 'dolor'];
        $request = new Request([], [], $attributes);

        $this->assertEquals($attributes['ipsum'], $request->getAttribute('ipsum'));
        $this->assertNull($request->getAttribute('dolor'));
        $this->assertEquals(1234, $request->getAttribute('test', 1234));
    }

    public function testWithAttribute(): void
    {
        $request = new Request();

        $new = $request->withAttribute('lorem', 'ipsum');

        $this->assertNotEquals($request, $new);
        $this->assertEquals('ipsum', $new->getAttribute('lorem'));
    }

    public function testWithoutAttribute(): void
    {
        $attributes = ['foo' => 'lorem', 'ipsum' => 'dolor'];
        $request = new Request([], [], $attributes);

        $new = $request->withoutAttribute('ipsum');

        $this->assertNotEquals($request, $new);
        $this->assertEquals(['foo' => 'lorem'], $new->getAttributes());
    }
}
