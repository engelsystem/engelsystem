<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Plugin;
use Illuminate\Database\Eloquent\Factories\Factory;

class PluginFactory extends Factory
{
    /** @var string */
    protected $model = Plugin::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'enabled' => $this->faker->boolean(),
            'version' => $this->faker->semver(),
        ];
    }
}
