<?php

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    /** @var string */
    protected $model = Group::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
