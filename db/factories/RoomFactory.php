<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    /** @var string */
    protected $model = Room::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'name'        => $this->faker->unique()->firstName(),
            'map_url'     => $this->faker->url(),
            'description' => $this->faker->text(),
        ];
    }
}
