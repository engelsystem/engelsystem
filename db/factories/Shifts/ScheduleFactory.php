<?php

namespace Database\Factories\Engelsystem\Models\Shifts;

use Engelsystem\Models\Shifts\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    /** @var string */
    protected $model = Schedule::class;

    /**
     * @return array
     */
    public function definition()
    {
        return [
            'name'           => $this->faker->words(4, true),
            'url'            => $this->faker->parse('https://{{safeEmailDomain}}/{{slug}}.xml'),
            'shift_type'     => $this->faker->numberBetween(1, 5),
            'minutes_before' => 15,
            'minutes_after'  => 15,
        ];
    }
}
