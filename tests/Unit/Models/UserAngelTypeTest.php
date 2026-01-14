<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(UserAngelType::class)]
#[CoversMethod(UserAngelType::class, 'angelType')]
#[CoversMethod(UserAngelType::class, 'confirmUser')]
#[CoversMethod(UserAngelType::class, 'getPivotAttributes')]
#[CoversMethod(UserAngelType::class, 'getIsConfirmedAttribute')]
class UserAngelTypeTest extends ModelTestCase
{
    protected User|Model $user;

    protected User|Model $confirmed;

    protected AngelType|Model $angeltype;

    public function testCreateDefault(): void
    {
        $model = new UserAngelType();
        $model->user()->associate($this->user);
        $model->angelType()->associate($this->angeltype);
        $model->save();

        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);

        $this->assertEquals($this->user->id, $model->user->id);
        $this->assertEquals($this->angeltype->id, $model->angelType->id);
        $this->assertNull($model->confirmUser);
        $this->assertFalse($model->supporter);
    }

    public function testCreateAssociation(): void
    {
        $this->user
            ->userAngelTypes()
            ->attach($this->angeltype, ['confirm_user_id' => $this->confirmed->id, 'supporter' => true]);

        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);

        $this->assertEquals($this->user->id, $model->user->id);
        $this->assertEquals($this->angeltype->id, $model->angelType->id);
        $this->assertEquals($this->confirmed->id, $model->confirmUser->id);
        $this->assertTrue($model->supporter);
    }

    public function testConfirmUser(): void
    {
        $model = new UserAngelType();
        $model->user()->associate($this->user);
        $model->angelType()->associate($this->angeltype);
        $model->confirmUser()->associate($this->confirmed);
        $model->save();

        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);
        $this->assertEquals($this->confirmed->id, $model->confirmUser->id);
    }

    public function testAngelType(): void
    {
        $model = new UserAngelType();
        $model->user()->associate($this->user);
        $model->angelType()->associate($this->angeltype);
        $model->save();

        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);
        $this->assertEquals($this->angeltype->id, $model->angelType->id);
    }

    public function testGetPivotAttributes(): void
    {
        $attributes = UserAngelType::getPivotAttributes();

        $this->assertContains('id', $attributes);
        $this->assertContains('supporter', $attributes);
        $this->assertContains('confirm_user_id', $attributes);
    }

    public function testGetIsConfirmedAttribute(): void
    {
        $this->angeltype->restricted = false;
        $this->angeltype->save();

        $model = new UserAngelType();
        $model->user()->associate($this->user);
        $model->angelType()->associate($this->angeltype);
        $model->save();

        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);
        $this->assertTrue($model->isConfirmed);

        $this->angeltype->restricted = true;
        $this->angeltype->save();
        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);
        $this->assertFalse($model->isConfirmed);

        $model->confirmUser()->associate($this->confirmed);
        $model->save();
        /** @var UserAngelType $model */
        $model = UserAngelType::find(1);
        $this->assertTrue($model->isConfirmed);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['id' => 42]);
        $this->confirmed = User::factory()->create(['id' => 1337]);
        $this->angeltype = AngelType::factory()->create(['id' => 21]);
    }
}
