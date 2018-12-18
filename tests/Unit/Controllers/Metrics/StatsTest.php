<?php

namespace Engelsystem\Test\Unit\Controllers\Metrics;

use Carbon\Carbon;
use Engelsystem\Controllers\Metrics\Stats;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Str;
use Psr\Log\LogLevel;

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
     * @covers \Engelsystem\Controllers\Metrics\Stats::sessions
     */
    public function testSessions()
    {
        $this->initDatabase();

        $this->database
            ->getConnection()
            ->table('sessions')
            ->insert([
                ['id' => 'asd', 'payload' => 'data', 'last_activity' => new Carbon('1 month ago')],
                ['id' => 'efg', 'payload' => 'lorem', 'last_activity' => new Carbon('55 minutes ago')],
                ['id' => 'hij', 'payload' => 'ipsum', 'last_activity' => new Carbon('3 seconds ago')],
                ['id' => 'klm', 'payload' => 'dolor', 'last_activity' => new Carbon()],
            ]);

        $stats = new Stats($this->database);
        $this->assertEquals(4, $stats->sessions());
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::logEntries
     */
    public function testLogEntries()
    {
        $this->initDatabase();

        (new LogEntry(['level' => LogLevel::INFO, 'message' => 'Some info']))->save();
        (new LogEntry(['level' => LogLevel::INFO, 'message' => 'Another info']))->save();
        (new LogEntry(['level' => LogLevel::CRITICAL, 'message' => 'A critical error!']))->save();
        (new LogEntry(['level' => LogLevel::DEBUG, 'message' => 'Verbose output!']))->save();
        (new LogEntry(['level' => LogLevel::INFO, 'message' => 'Shutdown initiated']))->save();
        (new LogEntry(['level' => LogLevel::WARNING, 'message' => 'Please be cautious']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(6, $stats->logEntries());
        $this->assertEquals(3, $stats->logEntries(LogLevel::INFO));
        $this->assertEquals(1, $stats->logEntries(LogLevel::DEBUG));
    }

    /**
     * @covers \Engelsystem\Controllers\Metrics\Stats::passwordResets
     */
    public function testPasswordResets()
    {
        $this->initDatabase();
        $this->addUsers();

        (new PasswordReset(['use_id' => 1, 'token' => 'loremIpsum123']))->save();
        (new PasswordReset(['use_id' => 3, 'token' => '5omeR4nd0mTok3N']))->save();

        $stats = new Stats($this->database);
        $this->assertEquals(2, $stats->passwordResets());
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
