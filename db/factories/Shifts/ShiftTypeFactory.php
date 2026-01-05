<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\Shifts;

use Engelsystem\Models\Shifts\ShiftType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftTypeFactory extends Factory
{
    /** @var string */
    protected $model = ShiftType::class; // phpcs:ignore

    public function definition(): array
    {
        $advanceMinutes = $this->faker->optional(.2)->numberBetween(1, 8 * 12) / 12; // 5 minutes steps
        return [
            'name' => $this->faker->unique()->firstName(),
            'description' => $this->faker->text(),
            'work_category' => 'A',
            'allows_accompanying_children' => false,
            'signup_advance_hours' => $advanceMinutes ? round($advanceMinutes) / 60 : null,
        ];
    }
}
