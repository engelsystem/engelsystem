<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseTest extends TestCase
{
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
     * @covers \Engelsystem\Http\Response::withStatus
     * @covers \Engelsystem\Http\Response::getReasonPhrase
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
}
