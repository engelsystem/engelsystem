<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\User;

use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /** @var string */
    protected $model = User::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'name'     => $this->faker->unique()->userName(),
            'password' => crypt(random_bytes(16), '$1$salt$'),
            'email'    => $this->faker->unique()->safeEmail(),
            'api_key'  => bin2hex(random_bytes(32)),
        ];
    }
}
