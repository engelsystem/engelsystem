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

    /**
     * @covers \Engelsystem\Models\User\State::getForceFoodAttribute
     */
    public function testGetForceFoodAttribute(): void
    {

        $state = new State();
        $this->assertFalse($state->force_food);

        $user = User::factory()->create();
        $state->force_food_by = $user->id;
        $this->assertTrue($state->force_food);
    }

    /**
     * @covers \Engelsystem\Models\User\State::scopeWhereForceFood
     */
    public function testScopeWhereForceFood(): void
    {
        $user = User::factory()->create();
        $state = $user->state;
        $state->force_food_by = null;
        $state->save();
        $this->assertCount(0, State::whereForceFood(true)->get());
        $this->assertCount(1, State::whereForceFood(false)->get());

        $state->force_food_by = $user->id;
        $state->save();
        $this->assertCount(1, State::whereForceFood(true)->get());
        $this->assertCount(0, State::whereForceFood(false)->get());
    }

    /**
     * @covers \Engelsystem\Models\User\State::forceFoodBy
     */
    public function testForceFoodBy(): void
    {
        $user = User::factory()->create();
        $force_food_by = User::factory()->create();

        $model = new State();
        $model->user()->associate($user);
        $model->save();

        $this->assertNull($model->forceFoodBy);

        $model->forceFoodBy()->associate($force_food_by);
        $model->save();

        $this->assertEquals($force_food_by->id, $model->forceFoodBy->id);
    }

    /**
     * @covers \Engelsystem\Models\User\State::getGotGoodieAttribute
     */
    public function testGetGotGoodieAttribute(): void
    {

        $state = new State();
        $this->assertFalse($state->got_goodie);

        $user = User::factory()->create();
        $state->got_goodie_by = $user->id;
        $this->assertTrue($state->got_goodie);
    }

    /**
     * @covers \Engelsystem\Models\User\State::scopeWhereGotGoodie
     */
    public function testScopeWhereGotGoodie(): void
    {
        $user = User::factory()->create();
        $state = $user->state;
        $state->got_goodie_by = null;
        $state->save();
        $this->assertCount(0, State::whereGotGoodie(true)->get());
        $this->assertCount(1, State::whereGotGoodie(false)->get());

        $state->got_goodie_by = $user->id;
        $state->save();
        $this->assertCount(1, State::whereGotGoodie(true)->get());
        $this->assertCount(0, State::whereGotGoodie(false)->get());
    }

    /**
     * @covers \Engelsystem\Models\User\State::gotGoodieBy
     */
    public function testGotGoodieBy(): void
    {
        $user = User::factory()->create();
        $got_goodie_by = User::factory()->create();

        $model = new State();
        $model->user()->associate($user);
        $model->save();

        $this->assertNull($model->gotGoodieBy);

        $model->gotGoodieBy()->associate($got_goodie_by);
        $model->save();

        $this->assertEquals($got_goodie_by->id, $model->gotGoodieBy->id);
    }
}
