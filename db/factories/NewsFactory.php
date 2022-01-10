<?php

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\News;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    /** @var string */
    protected $model = News::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'title'      => $this->faker->text(50),
            'text'       => $this->faker->realText(),
            'is_meeting' => $this->faker->boolean(),
            'is_pinned'  => $this->faker->boolean(.1),
            'user_id'    => User::factory(),
        ];
    }
}
