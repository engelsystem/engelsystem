<?php

namespace Engelsystem\Test\Unit\Controllers\Metrics;

use Engelsystem\Controllers\Metrics\Stats;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Str;

class StatsTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::newUsers
     * @covers \Engelsystem\Controllers\Metrics\Stats::getQuery
     * @covers \Engelsystem\Controllers\Metrics\Stats::__construct
     */
    public function testNewUsers()
    {
        $this->initDatabase();
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->newUsers());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::arrivedUsers
     */
    public function testArrivedUsers()
    {
        $this->initDatabase();
        $this->addUsers();

        $stats = new Stats($this->database);
        $this->assertEquals(3, $stats->arrivedUsers());
    }

    /**
     * Add some example users
     */
    protected function addUsers()
    {
        $this->addUser();
        $this->addUser();
        $this->addUser(['arrived' => 1]);
        $this->addUser(['arrived' => 1, 'active' => 1]);
        $this->addUser(['arrived' => 1, 'active' => 1]);
    }

    /**
     * @param array $state
     */
    protected function addUser(array $state = [])
    {
        $name = 'user_' . Str::random(5);

        $user = new User([
            'name'     => $name,
            'password' => '',
            'email'    => $name . '@engel.example.com',
            'api_key'  => '',
        ]);
        $user->save();

        $state = new State($state);
        $state->user()
            ->associate($user)
            ->save();
    }
}
