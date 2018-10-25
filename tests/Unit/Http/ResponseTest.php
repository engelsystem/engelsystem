<?php

namespace Engelsystem\Test\Unit\Http;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Http\Response;
use Engelsystem\Renderer\Renderer;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @covers \Engelsystem\Http\Response
     */
    public function testCreate()
    {
        $response = new Response();
        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @covers \Engelsystem\Http\Response::getReasonPhrase
     * @covers \Engelsystem\Http\Response::withStatus
     */
    public function testWithStatus()
    {
        $response = new Response();
        $newResponse = $response->withStatus(503);
        $this->assertNotEquals($response, $newResponse);
        $this->assertNotEquals('', $newResponse->getReasonPhrase());
        $this->assertEquals(503, $newResponse->getStatusCode());

        $newResponse = $response->withStatus(503, 'Foo');
        $this->assertEquals('Foo', $newResponse->getReasonPhrase());
    }

    /**
     * @covers \Engelsystem\Http\Response::withContent
     */
    public function testWithContent()
    {
        $response = new Response();
        $newResponse = $response->withContent('Lorem Ipsum?');

        $this->assertNotEquals($response, $newResponse);
        $this->assertEquals('Lorem Ipsum?', $newResponse->getContent());
    }

    /**
     * @covers \Engelsystem\Http\Response::withView
     */
    public function testWithView()
    {
        /** @var REnderer|MockObject $renderer */
        $renderer = $this->createMock(Renderer::class);

        $renderer->expects($this->once())
            ->method('render')
            ->with('foo', ['lorem' => 'ipsum'])
            ->willReturn('Foo ipsum!');

        $response = new Response('', 200, [], $renderer);
        $newResponse = $response->withView('foo', ['lorem' => 'ipsum'], 505, ['test' => 'er']);

        $this->assertNotEquals($response, $newResponse);
        $this->assertEquals('Foo ipsum!', $newResponse->getContent());
        $this->assertEquals(505, $newResponse->getStatusCode());
        $this->assertArraySubset(['test' => ['er']], $newResponse->getHeaders());
    }

    /**
     * @covers \Engelsystem\Http\Response::withView
     */
    public function testWithViewNoRenderer()
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new Response();
        $response->withView('foo');
    }

    /**
     * @covers \Engelsystem\Http\Response::redirectTo
     */
    public function testRedirectTo()
    {
        $response = new Response();
        $newResponse = $response->redirectTo('http://foo.bar/lorem', 301, ['test' => 'ing']);

        $this->assertNotEquals($response, $newResponse);
        $this->assertEquals(301, $newResponse->getStatusCode());
        $this->assertArraySubset(
            [
                'location' => ['http://foo.bar/lorem'],
                'test'     => ['ing'],
            ],
            $newResponse->getHeaders()
        );
    }
}
