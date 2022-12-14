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
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ResponseTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @covers \Engelsystem\Http\Response
     */
    public function testCreate(): void
    {
        $response = new Response();
        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @covers \Engelsystem\Http\Response::getReasonPhrase
     * @covers \Engelsystem\Http\Response::withStatus
     */
    public function testWithStatus(): void
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
    public function testWithContent(): void
    {
        $response = new Response();
        $newResponse = $response->withContent('Lorem Ipsum?');

        $this->assertNotEquals($response, $newResponse);
        $this->assertEquals('Lorem Ipsum?', $newResponse->getContent());
    }

    /**
     * @covers \Engelsystem\Http\Response::withView
     * @covers \Engelsystem\Http\Response::setRenderer
     */
    public function testWithView(): void
    {
        /** @var Renderer|MockObject $renderer */
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

        /** @var Renderer|MockObject $renderer */
        $anotherRenderer = $this->createMock(Renderer::class);
        $anotherRenderer->expects($this->once())
            ->method('render')
            ->with('bar')
            ->willReturn('Stuff');

        $response->setRenderer($anotherRenderer);
        $response = $response->withView('bar');
        $this->assertEquals('Stuff', $response->getContent());
    }

    /**
     * @covers \Engelsystem\Http\Response::withView
     */
    public function testWithViewNoRenderer(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new Response();
        $response->withView('foo');
    }

    /**
     * @covers \Engelsystem\Http\Response::redirectTo
     */
    public function testRedirectTo(): void
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

    /**
     * @covers \Engelsystem\Http\Response::with
     */
    public function testWith(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $response = new Response('', 200, [], null, $session);

        $response->with('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));

        $response->with('lorem', ['ipsum', 'dolor' => ['foo' => 'bar']]);
        $this->assertEquals(['ipsum', 'dolor' => ['foo' => 'bar']], $session->get('lorem'));

        $response->with('lorem', ['dolor' => ['test' => 'er']]);
        $this->assertEquals(['ipsum', 'dolor' => ['foo' => 'bar', 'test' => 'er']], $session->get('lorem'));
    }

    /**
     * @covers \Engelsystem\Http\Response::with
     */
    public function testWithNoSession(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new Response();
        $response->with('foo', 'bar');
    }

    /**
     * @covers \Engelsystem\Http\Response::withInput
     */
    public function testWithInput(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $response = new Response('', 200, [], null, $session);

        $response->withInput(['some' => 'value']);
        $this->assertEquals(['some' => 'value'], $session->get('form-data'));

        $response->withInput(['lorem' => 'ipsum']);
        $this->assertEquals(['lorem' => 'ipsum'], $session->get('form-data'));
    }

    /**
     * @covers \Engelsystem\Http\Response::withInput
     */
    public function testWithInputNoSession(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new Response();
        $response->withInput(['some' => 'value']);
    }
}
