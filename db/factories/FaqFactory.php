<?php

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    /** @var string */
    protected $model = Faq::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'question' => $this->faker->text(100),
            'text'     => $this->faker->optional(.5, '')->realText(),
        ];
    }
}
