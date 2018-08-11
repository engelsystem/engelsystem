<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Test\Unit\Http\Stub\MessageTraitImplementation;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Zend\Diactoros\Stream;

class MessageTraitTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\MessageTrait
     */
    public function testCreate()
    {
        $message = new MessageTraitImplementation();
        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertInstanceOf(SymfonyResponse::class, $message);
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::getProtocolVersion
     * @covers \Engelsystem\Http\MessageTrait::withProtocolVersion
     */
    public function testGetProtocolVersion()
    {
        $message = new MessageTraitImplementation();
        $newMessage = $message->withProtocolVersion('0.1');
        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals('0.1', $newMessage->getProtocolVersion());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::getHeaders
     */
    public function testGetHeaders()
    {
        $message = new MessageTraitImplementation();
        $newMessage = $message->withHeader('Foo', 'bar');

        $this->assertNotEquals($message, $newMessage);
        $this->assertArraySubset(['Foo' => ['bar']], $newMessage->getHeaders());

        $newMessage = $message->withHeader('lorem', ['ipsum', 'dolor']);
        $this->assertArraySubset(['lorem' => ['ipsum', 'dolor']], $newMessage->getHeaders());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::hasHeader
     */
    public function testHasHeader()
    {
        $message = new MessageTraitImplementation();
        $this->assertFalse($message->hasHeader('test'));

        $newMessage = $message->withHeader('test', '12345');
        $this->assertTrue($newMessage->hasHeader('Test'));
        $this->assertTrue($newMessage->hasHeader('test'));
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::getHeader
     */
    public function testGetHeader()
    {
        $message = new MessageTraitImplementation();
        $newMessage = $message->withHeader('foo', 'bar');

        $this->assertEquals(['bar'], $newMessage->getHeader('Foo'));
        $this->assertEquals([], $newMessage->getHeader('LoremIpsum'));
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::getHeaderLine
     */
    public function testGetHeaderLine()
    {
        $message = new MessageTraitImplementation();
        $newMessage = $message->withHeader('foo', ['bar', 'bla']);

        $this->assertEquals('', $newMessage->getHeaderLine('Lorem-Ipsum'));
        $this->assertEquals('bar,bla', $newMessage->getHeaderLine('Foo'));
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::withHeader
     */
    public function testWithHeader()
    {
        $message = new MessageTraitImplementation();
        $newMessage = $message->withHeader('foo', 'bar');

        $this->assertNotEquals($message, $newMessage);
        $this->assertArraySubset(['foo' => ['bar']], $newMessage->getHeaders());

        $newMessage = $newMessage->withHeader('Foo', ['lorem', 'ipsum']);
        $this->assertArraySubset(['Foo' => ['lorem', 'ipsum']], $newMessage->getHeaders());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::withAddedHeader
     */
    public function testWithAddedHeader()
    {
        $message = new MessageTraitImplementation();
        $newMessage = $message->withHeader('foo', 'bar');

        $this->assertNotEquals($message, $newMessage);
        $this->assertArraySubset(['foo' => ['bar']], $newMessage->getHeaders());

        $newMessage = $newMessage->withAddedHeader('Foo', ['lorem', 'ipsum']);
        $this->assertArraySubset(['Foo' => ['bar', 'lorem', 'ipsum']], $newMessage->getHeaders());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::withoutHeader
     */
    public function testWithoutHeader()
    {
        $message = (new MessageTraitImplementation())->withHeader('foo', 'bar');
        $this->assertTrue($message->hasHeader('foo'));

        $newMessage = $message->withoutHeader('Foo');
        $this->assertNotEquals($message, $newMessage);
        $this->assertFalse($newMessage->hasHeader('foo'));
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::getBody
     */
    public function testGetBody()
    {
        $message = (new MessageTraitImplementation())->setContent('Foo bar!');
        $body = $message->getBody();

        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertEquals('Foo bar!', $body->getContents());
    }

    /**
     * @covers \Engelsystem\Http\MessageTrait::withBody
     */
    public function testWithBody()
    {
        /** @var Stream $stream */
        $stream = new Stream('php://memory', 'wb+');
        $stream->write('Test content');
        $stream->rewind();

        $message = new MessageTraitImplementation();
        $newMessage = $message->withBody($stream);

        $this->assertNotEquals($message, $newMessage);
        $this->assertEquals('Test content', $newMessage->getContent());
    }
}
