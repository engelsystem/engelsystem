<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Carbon\Carbon;
use Engelsystem\Models\Worklog;
use Engelsystem\Models\User\User;

class WorklogTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\Worklog::creator
     */
    public function testCreator(): void
    {
        $user1 = User::create([
            'name'     => 'user1',
            'password' => '',
            'email'    => 'user1@example.com',
            'api_key'  => '',
        ]);
        $user2 = User::create([
            'name'     => 'user2',
            'password' => '',
            'email'    => 'user2@example.com',
            'api_key'  => '',
        ]);

        $worklog = new Worklog();
        $worklog->user()->associate($user1);
        $worklog->creator()->associate($user2);
        $worklog->hours = 4.2;
        $worklog->comment = 'Lorem ipsum';
        $worklog->worked_at = new Carbon();
        $worklog->save();

        $savedWorklog = Worklog::first();
        $this->assertEquals($user2->name, $savedWorklog->creator->name);
    }
}
