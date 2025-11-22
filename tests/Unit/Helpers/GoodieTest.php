<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Goodie;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use PDO;

class GoodieTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Helpers\Goodie::shiftScoreQuery
     */
    public function testShiftScoreQuery(): void
    {
        $result = Goodie::shiftScoreQuery();

        $this->assertEquals('0', $result->getValue(new SQLiteGrammar(new Connection(new PDO('sqlite::memory:')))));
    }

    /**
     * @covers \Engelsystem\Helpers\Goodie::userScore
     * @covers \Engelsystem\Helpers\Goodie::worklogScoreQuery
     */
    public function testUserScore(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        Worklog::factory()->create(['user_id' => $user->id, 'hours' => 42.23]);

        $result = Goodie::userScore($user);

        $this->assertEquals(42.23, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->initDatabase();
        $this->app->instance('config', new Config(['night_shifts' => ['enabled' => false]]));
    }
}
