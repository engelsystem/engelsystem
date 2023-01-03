<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\Shifts;

use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    /** @var string */
    protected $model = Shift::class; // phpcs:ignore

    public function definition(): array
    {
        $start = $this->faker->dateTimeThisMonth('2 weeks');
        return [
            'title'          => $this->faker->unique()->text(15),
            'description'    => $this->faker->text(),
            'url'            => $this->faker->url(),
            'start'          => $start,
            'end'            => $this->faker->dateTimeInInterval($start, '+3 hours'),
            'shift_type_id'  => ShiftType::factory(),
            'room_id'        => Room::factory(),
            'transaction_id' => $this->faker->optional()->uuid(),
            'created_by'     => User::factory(),
            'updated_by'     => $this->faker->optional(.3)->boolean() ? User::factory() : null,
        ];
    }
}
