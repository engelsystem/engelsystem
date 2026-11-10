<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\DataEvent;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(DataEvent::class, '__construct')]
class DataEventTest extends TestCase
{
    public function testCreation(): void
    {
        $data = new Collection(42);

        $event = new DataEvent('data.event', $data);
        $this->assertEquals('data.event', $event->getName());

        $this->assertEquals($data, $event->data);
    }
    public function testCreationCollect(): void
    {
        $data = ['foo' => 'bar'];

        $event = new DataEvent('data.event', $data);
        $this->assertEquals('data.event', $event->getName());

        $this->assertEquals(collect($data), $event->data);
    }
}
