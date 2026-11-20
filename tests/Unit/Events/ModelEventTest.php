<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\ModelEvent;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ModelEvent::class, '__construct')]
class ModelEventTest extends TestCase
{
    public function testConstruct(): void
    {
        $model = new Shift();

        $event = new ModelEvent('name', $model);

        $this->assertEquals('name', $event->getName());
        $this->assertEquals($model, $event->model);
    }
}
