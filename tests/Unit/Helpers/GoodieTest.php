<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Goodie;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;

class GoodieTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Helpers\Goodie::nightShiftsSumQuery
     */
    public function testNightShiftsSumQuery(): void
    {
        $result = Goodie::nightShiftsSumQuery();

        $this->assertEquals('0', $result->getValue(new SQLiteGrammar()));
    }

    /**
     * @covers \Engelsystem\Helpers\Goodie::userScore
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
        $this->app->instance('config', new Config(['night_shifts' => []]));
    }
}
