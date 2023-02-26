<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\User;

use Engelsystem\Models\User\Settings;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingsFactory extends Factory
{
    /** @var string */
    protected $model = Settings::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'language'        => $this->faker->locale(),
            'theme'           => $this->faker->numberBetween(1, 20),
            'email_human'     => $this->faker->boolean(),
            'email_messages'  => $this->faker->boolean(),
            'email_goody'     => $this->faker->boolean(),
            'email_shiftinfo' => $this->faker->boolean(),
            'email_news'      => $this->faker->boolean(),
            'mobile_show'     => $this->faker->boolean(),
        ];
    }
}
