<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\Shifts;

use Engelsystem\Models\Shifts\Schedule;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleShiftFactory extends Factory
{
    /** @var string */
    protected $model = ScheduleShift::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'shift_id' =>    Shift::factory(),
            'schedule_id' => Schedule::factory(),
            'guid' =>        $this->faker->uuid(),
        ];
    }
}
