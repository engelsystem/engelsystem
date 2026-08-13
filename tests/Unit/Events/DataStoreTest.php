<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\DataEvent;
use Engelsystem\Events\DataStore;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(DataStore::class, '__set')]
#[CoversMethod(DataStore::class, '__get')]
#[CoversMethod(DataStore::class, '__isset')]
#[CoversMethod(DataStore::class, '__unset')]
class DataStoreTest extends TestCase
{
    protected DataEvent $event;

    public function testSetGet(): void
    {
        $this->event->key = 'value';
        $this->assertEquals('value', $this->event->key);

        $this->assertNull($this->event->not_existent);
    }

    public function testChecksRemove(): void
    {
        $this->assertTrue(isset($this->event->foo));

        unset($this->event->foo);
        $this->assertFalse(isset($this->event->foo));
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->event = new DataEvent('data.event', ['foo' => 'bar']);
    }
}
