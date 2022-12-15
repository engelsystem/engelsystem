<?php

namespace Database\Factories\Engelsystem\Models\Shifts;

use Engelsystem\Models\Shifts\ShiftType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftTypeFactory extends Factory
{
    /** @var string */
    protected $model = ShiftType::class; // phpcs:ignore

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->unique()->firstName(),
            'description' => $this->faker->text(),
        ];
    }
}
