<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\User;

use Carbon\Carbon;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Models\ModelTest;

class UserStateTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\User\State::getArrivedAttribute
     */
    public function testGetArrivedAttribute(): void
    {
        $state = new State();
        $this->assertFalse($state->arrived);

        $state->arrival_date = Carbon::now();
        $this->assertTrue($state->arrived);
    }

    /**
     * @covers \Engelsystem\Models\User\State::scopeWhereArrived
     */
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
    /**
     * @covers \Engelsystem\Models\User\State::getForceActiveAttribute
     */
    public function testGetForceActiveAttribute(): void
    {

        $state = new State();
        $this->assertFalse($state->force_active);

        $user = User::factory()->create();
        $state->force_active_by = $user->id;
        $this->assertTrue($state->force_active);
    }

    /**
     * @covers \Engelsystem\Models\User\State::scopeWhereForceActive
     */
    public function testScopeWhereForceActive(): void
    {
        $user = User::factory()->create();
        $state = $user->state;
        $state->force_active_by = null;
        $state->save();
        $this->assertCount(0, State::whereForceActive(true)->get());
        $this->assertCount(1, State::whereForceActive(false)->get());

        $state->force_active_by = $user->id;
        $state->save();
        $this->assertCount(1, State::whereForceActive(true)->get());
        $this->assertCount(0, State::whereForceActive(false)->get());
    }

    /**
     * @covers \Engelsystem\Models\User\State::forceActiveBy
     */
    public function testForceActiveBy(): void
    {
        $user = User::factory()->create();
        $force_active_by = User::factory()->create();

        $model = new State();
        $model->user()->associate($user);
        $model->save();

        $this->assertNull($model->forceActiveBy);

        $model->forceActiveBy()->associate($force_active_by);
        $model->save();

        $this->assertEquals($force_active_by->id, $model->forceActiveBy->id);
    }
}
