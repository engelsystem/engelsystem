<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\User;

use Carbon\Carbon;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonalDataFactory extends Factory
{
    /** @var string */
    protected $model = PersonalData::class; // phpcs:ignore

    public function definition(): array
    {
        $arrival = $this->faker->optional()->dateTimeThisMonth('2 weeks');
        $departure = $this->faker->optional()->dateTimeThisMonth('2 weeks');

        return [
            'user_id' => User::factory(),
            'first_name' => $this->faker->optional(.7)->firstName(),
            'last_name' => $this->faker->optional()->lastName(),
            'pronoun' => $this->faker->optional(.3)->pronoun(),
            'shirt_size' => $this->faker->optional(.9)->shirtSize(),
            'planned_arrival_date' => $arrival ? Carbon::instance($arrival) : null,
            'planned_departure_date' => $departure ? Carbon::instance($departure) : null,
        ];
    }
}
