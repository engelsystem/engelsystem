<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\MessageTrait;
use Engelsystem\Test\Unit\Http\Stub\MessageTraitRequestImplementation;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(MessageTrait::class, 'withProtocolVersion')]
#[CoversMethod(MessageTrait::class, 'getHeaders')]
#[CoversMethod(MessageTrait::class, 'withBody')]
class MessageTraitRequestTest extends TestCase
{
    public function testWithProtocolVersion(): void
    {
        $message = new MessageTraitRequestImplementation();
        $newMessage = $message->withProtocolVersion('0.1');
        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals('0.1', $newMessage->getProtocolVersion());
    }

    public function testGetHeaders(): void
    {
        $message = new MessageTraitRequestImplementation();
        $newMessage = $message->withHeader('lorem', 'ipsum');

        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals(['ipsum'], $newMessage->getHeaders()['lorem']);
    }

    public function testWithBody(): void
    {
        $stream = Stream::create('Test content');
        $message = new MessageTraitRequestImplementation();
        $newMessage = $message->withBody($stream);

        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals('Test content', $newMessage->getContent());
    }
}
