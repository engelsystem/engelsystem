<?php

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Privilege;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrivilegeFactory extends Factory
{
    /** @var string */
    protected $model = Privilege::class; // phpcs:ignore

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->unique()->word(),
            'description' => $this->faker->text(),
        ];
    }
}
