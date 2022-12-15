<?php

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    /** @var string */
    protected $model = Group::class; // phpcs:ignore

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}
