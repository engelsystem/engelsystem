<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\NewsEvent;
use Engelsystem\Models\News;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(NewsEvent::class, '__construct')]
class NewsEventTest extends TestCase
{
    public function testConstruct(): void
    {
        $model = new News();
        $event = new NewsEvent('news.created', $model, true);

        $this->assertEquals('news.created', $event->getName());
        $this->assertEquals($model, $event->news);
        $this->assertTrue($event->sendNotification);

        $event = new NewsEvent('news.updated', $model, false);
        $this->assertFalse($event->sendNotification);
    }
}
