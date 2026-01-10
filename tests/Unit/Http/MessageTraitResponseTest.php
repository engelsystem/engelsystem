<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\MessageTrait;
use Engelsystem\Test\Unit\Http\Stub\MessageTraitResponseImplementation;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

#[CoversTrait(MessageTrait::class)]
#[CoversMethod(MessageTrait::class, 'getProtocolVersion')]
#[CoversMethod(MessageTrait::class, 'withProtocolVersion')]
#[CoversMethod(MessageTrait::class, 'getHeaders')]
#[CoversMethod(MessageTrait::class, 'hasHeader')]
#[CoversMethod(MessageTrait::class, 'getHeader')]
#[CoversMethod(MessageTrait::class, 'getHeaderLine')]
#[CoversMethod(MessageTrait::class, 'withHeader')]
#[CoversMethod(MessageTrait::class, 'withAddedHeader')]
#[CoversMethod(MessageTrait::class, 'withoutHeader')]
#[CoversMethod(MessageTrait::class, 'getBody')]
#[CoversMethod(MessageTrait::class, 'withBody')]
class MessageTraitResponseTest extends TestCase
{
    public function testCreate(): void
    {
        $message = new MessageTraitResponseImplementation();
        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertInstanceOf(SymfonyResponse::class, $message);
    }

    public function testGetProtocolVersion(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withProtocolVersion('0.1');
        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals('0.1', $newMessage->getProtocolVersion());
    }

    public function testGetHeaders(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withHeader('Foo', 'bar');

        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals(['bar'], $newMessage->getHeaders()['Foo']);

        $newMessage = $message->withHeader('lorem', ['ipsum', 'dolor']);
        $this->assertEquals(['ipsum', 'dolor'], $newMessage->getHeaders()['lorem']);
    }

    public function testHasHeader(): void
    {
        $message = new MessageTraitResponseImplementation();
        $this->assertFalse($message->hasHeader('test'));

        $newMessage = $message->withHeader('test', '12345');
        $this->assertTrue($newMessage->hasHeader('Test'));
        $this->assertTrue($newMessage->hasHeader('test'));
    }

    public function testGetHeader(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withHeader('foo', 'bar');

        $this->assertEquals(['bar'], $newMessage->getHeader('Foo'));
        $this->assertEquals([], $newMessage->getHeader('LoremIpsum'));

        $newMessage = $message
            ->withHeader('foo', 'bar')
            ->withAddedHeader('foo', 'batz');
        $this->assertEquals(['bar', 'batz'], $newMessage->getHeader('foo'));
    }

    public function testGetHeaderLine(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withHeader('foo', ['bar', 'bla']);

        $this->assertEquals('', $newMessage->getHeaderLine('Lorem-Ipsum'));
        $this->assertEquals('bar,bla', $newMessage->getHeaderLine('Foo'));
    }

    public function testWithHeader(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withHeader('foo', 'bar');

        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals(['bar'], $newMessage->getHeaders()['foo']);

        $newMessage = $newMessage->withHeader('Foo', ['lorem', 'ipsum']);
        $this->assertEquals(['lorem', 'ipsum'], $newMessage->getHeaders()['Foo']);
    }

    public function testWithAddedHeader(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withHeader('foo', 'bar');

        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals(['bar'], $newMessage->getHeaders()['foo']);

        $newMessage = $newMessage->withAddedHeader('Foo', ['lorem', 'ipsum']);
        $this->assertEquals(['bar', 'lorem', 'ipsum'], $newMessage->getHeaders()['Foo']);
    }

    public function testWithoutHeader(): void
    {
        $message = (new MessageTraitResponseImplementation())->withHeader('foo', 'bar');
        $this->assertTrue($message->hasHeader('foo'));

        $newMessage = $message->withoutHeader('Foo');
        $this->assertNotEquals($message, $newMessage);
        $this->assertFalse($newMessage->hasHeader('foo'));
    }

    public function testGetBody(): void
    {
        $message = (new MessageTraitResponseImplementation())->setContent('Foo bar!');
        $body = $message->getBody();

        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertEquals('Foo bar!', $body->getContents());
    }

    public function testWithBody(): void
    {
        $stream = Stream::create('Test content');
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withBody($stream);

        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals('Test content', $newMessage->getContent());
    }
}
