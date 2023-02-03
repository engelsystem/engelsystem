<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\Shifts;

use Engelsystem\Models\Shifts\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    /** @var string */
    protected $model = Schedule::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'name'           => $this->faker->unique()->words(4, true),
            'url'            => $this->faker->parse('https://{{safeEmailDomain}}/{{slug}}.xml'),
            'shift_type'     => $this->faker->numberBetween(1, 5),
            'minutes_before' => 15,
            'minutes_after'  => 15,
        ];
    }
}
