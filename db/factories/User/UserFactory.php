<?php

namespace Database\Factories\Engelsystem\Models\User;

use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /** @var string */
    protected $model = User::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'name'     => $this->faker->unique()->userName(),
            'password' => password_hash($this->faker->password(), PASSWORD_DEFAULT),
            'email'    => $this->faker->unique()->safeEmail(),
            'api_key'  => md5($this->faker->unique()->password()),
        ];
    }
}
