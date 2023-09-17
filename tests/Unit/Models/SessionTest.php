<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\Session;

/**
 * This class provides tests for the Session model
 */
class SessionTest extends ModelTest
{
    /**
     * Tests that a Session can be created and loaded
     *
     * @covers \Engelsystem\Models\Session
     */
    public function testCreate(): void
    {
        Session::create([
            'id' => 'foo',
            'payload' => 'lorem ipsum',
            'last_activity' => Carbon::now(),
        ]);
        Session::create([
            'id' => 'bar',
            'last_activity' => Carbon::now(),
        ]);

        $session = Session::find('foo');
        $this->assertNotNull($session);
        $this->assertEquals('lorem ipsum', $session->payload);
    }
}
