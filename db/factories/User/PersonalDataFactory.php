<?php

namespace Database\Factories\Engelsystem\Models\User;

use Carbon\Carbon;
use Engelsystem\Models\User\PersonalData;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonalDataFactory extends Factory
{
    /** @var string */
    protected $model = PersonalData::class;

    /**
     * @return array
     */
    public function definition()
    {
        $arrival = $this->faker->optional()->dateTimeThisMonth('2 weeks');
        $departure = $this->faker->optional()->dateTimeThisMonth('2 weeks');

        return [
            'first_name' => $this->faker->optional(.7)->firstName(),
            'last_name' => $this->faker->optional()->lastName(),
            'pronoun' => $this->faker->optional(.3)->pronoun(),
            'shirt_size' => $this->faker->optional(.9)->shirtSize(),
            'planned_arrival_date' => $arrival ? Carbon::instance($arrival) : null,
            'planned_departure_date' => $departure ? Carbon::instance($departure) : null,
        ];
    }
}
