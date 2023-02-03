<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\User;

use Engelsystem\Models\User\PasswordReset;
use Illuminate\Database\Eloquent\Factories\Factory;

class PasswordResetFactory extends Factory
{
    /** @var string */
    protected $model = PasswordReset::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'token' => bin2hex(random_bytes(16)),
        ];
    }
}
