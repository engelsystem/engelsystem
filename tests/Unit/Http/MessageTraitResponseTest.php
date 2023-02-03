<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Test\Unit\Http\Stub\MessageTraitResponseImplementation;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MessageTraitResponseTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @covers \Engelsystem\Http\MessageTrait
     */
    public function testCreate(): void
    {
        $message = new MessageTraitResponseImplementation();
        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertInstanceOf(SymfonyResponse::class, $message);
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::getProtocolVersion
     * @covers \Engelsystem\Http\MessageTrait::withProtocolVersion
     */
    public function testGetProtocolVersion(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withProtocolVersion('0.1');
        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals('0.1', $newMessage->getProtocolVersion());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::getHeaders
     */
    public function testGetHeaders(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withHeader('Foo', 'bar');

        $this->assertNotEquals($message, $newMessage);
        $this->assertArraySubset(['Foo' => ['bar']], $newMessage->getHeaders());

        $newMessage = $message->withHeader('lorem', ['ipsum', 'dolor']);
        $this->assertArraySubset(['lorem' => ['ipsum', 'dolor']], $newMessage->getHeaders());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::hasHeader
     */
    public function testHasHeader(): void
    {
        $message = new MessageTraitResponseImplementation();
        $this->assertFalse($message->hasHeader('test'));

        $newMessage = $message->withHeader('test', '12345');
        $this->assertTrue($newMessage->hasHeader('Test'));
        $this->assertTrue($newMessage->hasHeader('test'));
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::getHeader
     */
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

    /**
     * @covers \Engelsystem\Http\MessageTrait::getHeaderLine
     */
    public function testGetHeaderLine(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withHeader('foo', ['bar', 'bla']);

        $this->assertEquals('', $newMessage->getHeaderLine('Lorem-Ipsum'));
        $this->assertEquals('bar,bla', $newMessage->getHeaderLine('Foo'));
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::withHeader
     */
    public function testWithHeader(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withHeader('foo', 'bar');

        $this->assertNotEquals($message, $newMessage);
        $this->assertArraySubset(['foo' => ['bar']], $newMessage->getHeaders());

        $newMessage = $newMessage->withHeader('Foo', ['lorem', 'ipsum']);
        $this->assertArraySubset(['Foo' => ['lorem', 'ipsum']], $newMessage->getHeaders());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::withAddedHeader
     */
    public function testWithAddedHeader(): void
    {
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withHeader('foo', 'bar');

        $this->assertNotEquals($message, $newMessage);
        $this->assertArraySubset(['foo' => ['bar']], $newMessage->getHeaders());

        $newMessage = $newMessage->withAddedHeader('Foo', ['lorem', 'ipsum']);
        $this->assertArraySubset(['Foo' => ['bar', 'lorem', 'ipsum']], $newMessage->getHeaders());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::withoutHeader
     */
    public function testWithoutHeader(): void
    {
        $message = (new MessageTraitResponseImplementation())->withHeader('foo', 'bar');
        $this->assertTrue($message->hasHeader('foo'));

        $newMessage = $message->withoutHeader('Foo');
        $this->assertNotEquals($message, $newMessage);
        $this->assertFalse($newMessage->hasHeader('foo'));
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::getBody
     */
    public function testGetBody(): void
    {
        $message = (new MessageTraitResponseImplementation())->setContent('Foo bar!');
        $body = $message->getBody();

        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertEquals('Foo bar!', $body->getContents());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::withBody
     */
    public function testWithBody(): void
    {
        $stream = Stream::create('Test content');
        $message = new MessageTraitResponseImplementation();
        $newMessage = $message->withBody($stream);

        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals('Test content', $newMessage->getContent());
    }
}
