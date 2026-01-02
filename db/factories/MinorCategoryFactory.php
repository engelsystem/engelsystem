<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\MinorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MinorCategoryFactory extends Factory
{
    /** @var string */
    protected $model = MinorCategory::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'name'                    => $this->faker->unique()->word() . ' Minor',
            'description'             => $this->faker->optional()->sentence(),
            'min_shift_start_hour'    => $this->faker->optional()->numberBetween(6, 10),
            'max_shift_end_hour'      => $this->faker->optional()->numberBetween(18, 22),
            'max_hours_per_day'       => $this->faker->optional()->numberBetween(4, 8),
            'allowed_work_categories' => $this->faker->randomElements(['A', 'B', 'C'], $this->faker->numberBetween(1, 3)),
            'can_fill_slot'           => $this->faker->boolean(80),
            'requires_supervisor'     => $this->faker->boolean(70),
            'can_self_signup'         => $this->faker->boolean(60),
            'display_order'           => $this->faker->numberBetween(0, 10),
            'is_active'               => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the category is active
     */
    public function active(): self
    {
        return $this->state(fn(array $attributes) => ['is_active' => true]);
    }

    /**
     * Indicate that the category is inactive
     */
    public function inactive(): self
    {
        return $this->state(fn(array $attributes) => ['is_active' => false]);
    }

    /**
     * Configure category for young minors (strict restrictions)
     */
    public function youngMinor(): self
    {
        return $this->state(fn(array $attributes) => [
            'name'                    => 'Young Minor (Under 16)',
            'min_shift_start_hour'    => 8,
            'max_shift_end_hour'      => 18,
            'max_hours_per_day'       => 4,
            'allowed_work_categories' => ['A'],
            'requires_supervisor'     => true,
        ]);
    }

    /**
     * Configure category for older minors (relaxed restrictions)
     */
    public function olderMinor(): self
    {
        return $this->state(fn(array $attributes) => [
            'name'                    => 'Older Minor (16-17)',
            'min_shift_start_hour'    => 6,
            'max_shift_end_hour'      => 22,
            'max_hours_per_day'       => 8,
            'allowed_work_categories' => ['A', 'B'],
            'requires_supervisor'     => false,
        ]);
    }
}
