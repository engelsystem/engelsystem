<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events;

use Engelsystem\Events\OAuth2Event;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(OAuth2Event::class, '__construct')]
class OAuth2EventTest extends TestCase
{
    public function testConstruct(): void
    {
        $data = new Collection(['foo' => 'bar']);

        $event = new OAuth2Event(OAuth2Event::$LOGIN, 'tester', $data);

        $this->assertEquals(OAuth2Event::$LOGIN, $event->getName());
        $this->assertEquals('tester', $event->providerName);
        $this->assertEquals($data, $event->userdata);
    }
}
