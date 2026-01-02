<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserGuardian;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGuardianFactory extends Factory
{
    /** @var string */
    protected $model = UserGuardian::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'minor_user_id'      => User::factory(),
            'guardian_user_id'   => User::factory(),
            'is_primary'         => $this->faker->boolean(50),
            'relationship_type'  => $this->faker->randomElement(['parent', 'legal_guardian', 'delegated']),
            'can_manage_account' => $this->faker->boolean(80),
            'valid_from'         => null,
            'valid_until'        => null,
        ];
    }

    /**
     * Indicate that this is a primary guardian
     */
    public function primary(): self
    {
        return $this->state(fn(array $attributes) => ['is_primary' => true]);
    }

    /**
     * Indicate that this guardian can manage the account
     */
    public function canManage(): self
    {
        return $this->state(fn(array $attributes) => ['can_manage_account' => true]);
    }

    /**
     * Indicate that this guardian cannot manage the account
     */
    public function cannotManage(): self
    {
        return $this->state(fn(array $attributes) => ['can_manage_account' => false]);
    }

    /**
     * Set parent as relationship type
     */
    public function parent(): self
    {
        return $this->state(fn(array $attributes) => ['relationship_type' => 'parent']);
    }

    /**
     * Set legal_guardian as relationship type
     */
    public function legalGuardian(): self
    {
        return $this->state(fn(array $attributes) => ['relationship_type' => 'legal_guardian']);
    }

    /**
     * Set delegated as relationship type
     */
    public function delegated(): self
    {
        return $this->state(fn(array $attributes) => ['relationship_type' => 'delegated']);
    }

    /**
     * Set a validity period for the guardian relationship
     */
    public function withValidityPeriod(?Carbon $from = null, ?Carbon $until = null): self
    {
        return $this->state(fn(array $attributes) => [
            'valid_from'  => $from ?? Carbon::now()->subMonth(),
            'valid_until' => $until ?? Carbon::now()->addYear(),
        ]);
    }

    /**
     * Create an expired guardian relationship
     */
    public function expired(): self
    {
        return $this->state(fn(array $attributes) => [
            'valid_from'  => Carbon::now()->subYear(),
            'valid_until' => Carbon::now()->subDay(),
        ]);
    }

    /**
     * Create a future guardian relationship (not yet valid)
     */
    public function future(): self
    {
        return $this->state(fn(array $attributes) => [
            'valid_from'  => Carbon::now()->addDay(),
            'valid_until' => Carbon::now()->addYear(),
        ]);
    }
}
