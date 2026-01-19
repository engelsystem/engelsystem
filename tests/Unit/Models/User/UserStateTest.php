<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\User;

use Carbon\Carbon;
use Engelsystem\Models\User\State;
use Engelsystem\Test\Unit\Models\ModelTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(State::class, 'getArrivedAttribute')]
#[CoversMethod(State::class, 'scopeWhereArrived')]
class UserStateTest extends ModelTestCase
{
    public function testGetArrivedAttribute(): void
    {
        $state = new State();
        $this->assertFalse($state->arrived);

        $state->arrival_date = Carbon::now();
        $this->assertTrue($state->arrived);
    }

    public function testScopeWhereArrived(): void
    {
        $state = State::factory()->create([
            'arrival_date' => null,
        ]);
        $this->assertCount(0, State::whereArrived(true)->get());
        $this->assertCount(1, State::whereArrived(false)->get());

        $state->arrival_date = Carbon::now();
        $state->save();
        $this->assertCount(1, State::whereArrived(true)->get());
        $this->assertCount(0, State::whereArrived(false)->get());
    }
}
