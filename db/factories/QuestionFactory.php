<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Carbon\Carbon;
use Engelsystem\Models\Question;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    /** @var string */
    protected $model = Question::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'text'        => $this->faker->text(100),
            'answerer_id' => $this->faker->optional()->passthrough(User::factory()),
            'answer'      => function (array $attributes) {
                return $attributes['answerer_id'] ? $this->faker->text() : null;
            },
            'answered_at' => function (array $attributes) {
                return $attributes['answerer_id'] ? Carbon::instance($this->faker->dateTimeThisMonth()) : null;
            },
        ];
    }
}
