<?php

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsCommentFactory extends Factory
{
    /** @var string */
    protected $model = NewsComment::class; // phpcs:ignore

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'news_id' => News::factory(),
            'user_id' => User::factory(),
            'text'    => $this->faker->text(),
        ];
    }
}
