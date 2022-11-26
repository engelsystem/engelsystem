<?php

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Privilege;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrivilegeFactory extends Factory
{
    /** @var string */
    protected $model = Privilege::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'name'        => $this->faker->unique()->word(),
            'description' => $this->faker->text(),
        ];
    }
}
