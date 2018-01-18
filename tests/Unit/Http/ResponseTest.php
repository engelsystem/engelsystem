<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Response;
use PHPUnit\Framework\TestCase;
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
    }
}
