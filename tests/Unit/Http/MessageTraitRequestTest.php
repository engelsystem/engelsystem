<?php

namespace Engelsystem\Test\Unit\Http;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Test\Unit\Http\Stub\MessageTraitRequestImplementation;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;

class MessageTraitRequestTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @covers \Engelsystem\Http\MessageTrait::withProtocolVersion
     */
    public function testWithProtocolVersion(): void
    {
        $message = new MessageTraitRequestImplementation();
        $newMessage = $message->withProtocolVersion('0.1');
        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals('0.1', $newMessage->getProtocolVersion());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::getHeaders
     */
    public function testGetHeaders(): void
    {
        $message = new MessageTraitRequestImplementation();
        $newMessage = $message->withHeader('lorem', 'ipsum');

        $this->assertNotEquals($message, $newMessage);
        $this->assertArraySubset(['lorem' => ['ipsum']], $newMessage->getHeaders());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::withBody
     */
    public function testWithBody(): void
    {
        $stream = Stream::create('Test content');
        $message = new MessageTraitRequestImplementation();
        $newMessage = $message->withBody($stream);

        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals('Test content', $newMessage->getContent());
    }
}
