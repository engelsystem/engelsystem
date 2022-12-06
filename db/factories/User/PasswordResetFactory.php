<?php

namespace Database\Factories\Engelsystem\Models\User;

use Engelsystem\Models\User\PasswordReset;
use Illuminate\Database\Eloquent\Factories\Factory;

class PasswordResetFactory extends Factory
{
    /** @var string */
    protected $model = PasswordReset::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'token' => bin2hex(random_bytes(16)),
        ];
    }
}
