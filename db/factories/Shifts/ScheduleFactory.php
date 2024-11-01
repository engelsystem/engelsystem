<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\Shifts;

use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ShiftType;
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
            'shift_type'     => ShiftType::factory(),
            'needed_from_shift_type' => $this->faker->boolean(.2),
            'minutes_before' => 15,
            'minutes_after'  => 15,
        ];
    }
}
