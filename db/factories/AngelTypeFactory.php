<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\AngelType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AngelTypeFactory extends Factory
{
    /** @var string */
    protected $model = AngelType::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'name'        => $this->faker->unique()->firstName(),
            'description' => $this->faker->text(),

            'contact_name'  => $this->faker->firstName(),
            'contact_dect'  => $this->faker->randomNumber(4),
            'contact_email' => $this->faker->email(),

            'restricted'              => $this->faker->boolean(),
            'requires_driver_license' => $this->faker->boolean(),
            'requires_ifsg_certificate' => $this->faker->boolean(),
            'shift_self_signup'       => $this->faker->boolean(),
            'show_on_dashboard'       => $this->faker->boolean(),
            'hide_register'           => $this->faker->boolean(),
            'hide_on_shift_view'      => $this->faker->boolean(),
        ];
    }
}
