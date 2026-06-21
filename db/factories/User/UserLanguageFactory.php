<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\User;

use Engelsystem\Models\User\User;
use Engelsystem\Models\User\UserLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserLanguageFactory extends Factory
{
    /** @var string */
    protected $model = UserLanguage::class; // phpcs:ignore

    public function definition(): array
    {
        $languages = ['en', 'de', 'fr', 'es', 'nl', 'pt-BR', 'zh-CN', 'ja', 'ko', 'ru'];

        return [
            'user_id'       => User::factory(),
            'language_code' => $this->faker->randomElement($languages),
            'is_native'     => $this->faker->boolean(30),
        ];
    }

    /**
     * Mark language as native
     */
    public function native(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_native' => true,
        ]);
    }
}
