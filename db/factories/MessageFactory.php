<?php

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    /** @var string */
    protected $model = Message::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'receiver_id' => User::factory(),
            'read'        => $this->faker->boolean(),
            'text'        => $this->faker->text(),
        ];
    }
}
