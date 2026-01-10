<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\Session;
use Engelsystem\Models\User\User;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * This class provides tests for the Session model
 */
#[CoversClass(Session::class)]
class SessionTest extends ModelTestCase
{
    /**
     * Tests that a Session can be created and loaded
     */
    public function testCreate(): void
    {
        $user = User::factory()->create();
        Session::create([
            'id' => 'foo',
            'payload' => 'lorem ipsum',
            'user_id' => $user->id,
            'last_activity' => Carbon::now(),
        ]);
        Session::create([
            'id' => 'bar',
            'last_activity' => Carbon::now(),
        ]);

        $session = Session::find('foo');
        $this->assertNotNull($session);
        $this->assertEquals('lorem ipsum', $session->payload);
        $this->assertInstanceOf(User::class, $session->user);

        $session = Session::find('bar');
        $this->assertNull($session->user);
    }
}
