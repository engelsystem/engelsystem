<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Session;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFactory extends Factory
{
    /** @var string */
    protected $model = Session::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'id' => $this->faker->lexify('??????????'),
            'payload' => $this->faker->text(100),
        ];
    }
}
