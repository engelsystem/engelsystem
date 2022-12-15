<?php

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAngelTypeFactory extends Factory
{
    /** @var string */
    protected $model = UserAngelType::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'angel_type_id'   => AngelType::factory(),
            'confirm_user_id' => $this->faker->optional()->passthrough(User::factory()),
            'supporter'       => $this->faker->boolean(),
        ];
    }
}
