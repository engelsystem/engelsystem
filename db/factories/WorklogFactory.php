<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorklogFactory extends Factory
{
    /** @var string */
    protected $model = Worklog::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'creator_id'  => User::factory(),
            'hours'       => $this->faker->randomFloat(2, 0.01, 10),
            'description' => $this->faker->text(30),
            'worked_at'   => $this->faker->dateTimeThisMonth(),
            'night_shift' => $this->faker->boolean(),
        ];
    }
}
