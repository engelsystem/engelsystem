<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Response;
use Engelsystem\Renderer\Renderer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[CoversClass(Response::class)]
#[CoversMethod(Response::class, 'getReasonPhrase')]
#[CoversMethod(Response::class, 'withStatus')]
#[CoversMethod(Response::class, 'withContent')]
#[CoversMethod(Response::class, 'withView')]
#[CoversMethod(Response::class, 'setRenderer')]
#[CoversMethod(Response::class, 'redirectTo')]
#[CoversMethod(Response::class, 'with')]
#[CoversMethod(Response::class, 'withInput')]
class ResponseTest extends TestCase
{
    public function testCreate(): void
    {
        $response = new Response();
        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

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

    public function testWithContent(): void
    {
        $response = new Response();
        $newResponse = $response->withContent('Lorem Ipsum?');

        $this->assertNotEquals($response, $newResponse);
        $this->assertEquals('Lorem Ipsum?', $newResponse->getContent());
    }

    public function testWithView(): void
    {
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
        $this->assertEquals(['er'], $newResponse->getHeaders()['test']);

        $anotherRenderer = $this->createMock(Renderer::class);
        $anotherRenderer->expects($this->once())
            ->method('render')
            ->with('bar')
            ->willReturn('Stuff');

        $response->setRenderer($anotherRenderer);
        $response = $response->withView('bar');
        $this->assertEquals('Stuff', $response->getContent());
    }

    public function testWithViewNoRenderer(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new Response();
        $response->withView('foo');
    }

    public function testRedirectTo(): void
    {
        $response = new Response();
        $newResponse = $response->redirectTo('https://foo.bar/lorem', 301, ['test' => 'ing']);

        $this->assertNotEquals($response, $newResponse);
        $this->assertEquals(301, $newResponse->getStatusCode());
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys(
            [
                'location' => ['https://foo.bar/lorem'],
                'test'     => ['ing'],
            ],
            $newResponse->getHeaders(),
            ['location', 'test'],
        );
    }

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

    public function testWithNoSession(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new Response();
        $response->with('foo', 'bar');
    }

    public function testWithInput(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $response = new Response('', 200, [], null, $session);

        $response->withInput(['some' => 'value']);
        $this->assertEquals('value', $session->get('form-data-some'));

        $response->withInput(['lorem' => 'ipsum']);
        $this->assertEquals('ipsum', $session->get('form-data-lorem'));
    }

    public function testWithInputNoSession(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new Response();
        $response->withInput(['some' => 'value']);
    }
}
