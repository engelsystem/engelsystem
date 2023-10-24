<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    /** @var string */
    protected $model = Location::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'name'        => $this->faker->unique()->firstName(),
            'map_url'     => $this->faker->url(),
            'description' => $this->faker->text(),
            'dect'        => $this->faker->optional()->numberBetween(1000, 9999),
        ];
    }
}
