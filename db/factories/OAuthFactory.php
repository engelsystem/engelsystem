<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\OAuth;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OAuthFactory extends Factory
{
    /** @var class-string */
    protected $model = OAuth::class; // phpcs:ignore

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'provider' => $this->faker->unique()->word(),
            'identifier' => $this->faker->unique()->word(),
            'access_token' => $this->faker->unique()->word(),
            'refresh_token' => $this->faker->unique()->word(),
            'expires_at' => $this->faker->dateTimeInInterval('+5 days', '+3 months')->format('Y-m-d'),
        ];
    }
}
