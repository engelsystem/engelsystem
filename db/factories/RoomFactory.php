<?php

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    /** @var string */
    protected $model = Room::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'name'        => $this->faker->unique()->firstName(),
            'map_url'     => $this->faker->url(),
            'description' => $this->faker->text(),
        ];
    }
}
