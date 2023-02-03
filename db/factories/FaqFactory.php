<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    /** @var string */
    protected $model = Faq::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'question' => $this->faker->text(100),
            'text'     => $this->faker->optional(.5, '')->realText(),
        ];
    }
}
