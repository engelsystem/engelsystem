<?php

namespace Database\Factories\Engelsystem\Models\User;

use Engelsystem\Models\User\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    /** @var string */
    protected $model = Contact::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'dect'   => $this->faker->optional()->numberBetween(1000, 9999),
            'email'  => $this->faker->unique()->optional()->safeEmail(),
            'mobile' => $this->faker->optional(.2)->phoneNumber(),
        ];
    }
}
