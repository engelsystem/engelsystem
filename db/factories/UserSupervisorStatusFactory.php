<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\User\User;
use Engelsystem\Models\UserSupervisorStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSupervisorStatusFactory extends Factory
{
    /** @var string */
    protected $model = UserSupervisorStatus::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'user_id'                        => User::factory(),
            'willing_to_supervise'           => $this->faker->boolean(30),
            'supervision_training_completed' => $this->faker->boolean(20),
        ];
    }

    /**
     * Indicate that the user is willing to supervise
     */
    public function willing(): self
    {
        return $this->state(fn(array $attributes) => ['willing_to_supervise' => true]);
    }

    /**
     * Indicate that the user is not willing to supervise
     */
    public function notWilling(): self
    {
        return $this->state(fn(array $attributes) => ['willing_to_supervise' => false]);
    }

    /**
     * Indicate that the user has completed training
     */
    public function trained(): self
    {
        return $this->state(fn(array $attributes) => ['supervision_training_completed' => true]);
    }

    /**
     * Indicate that the user has not completed training
     */
    public function notTrained(): self
    {
        return $this->state(fn(array $attributes) => ['supervision_training_completed' => false]);
    }

    /**
     * Configure a fully qualified supervisor (willing + trained)
     */
    public function qualifiedSupervisor(): self
    {
        return $this->state(fn(array $attributes) => [
            'willing_to_supervise'           => true,
            'supervision_training_completed' => true,
        ]);
    }
}
