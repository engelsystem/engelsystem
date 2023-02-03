<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class NeededAngelTypeFactory extends Factory
{
    /** @var string */
    protected $model = NeededAngelType::class; // phpcs:ignore

    public function definition(): array
    {
        $forRoom = $this->faker->boolean();

        return [
            'room_id'       => $forRoom ? Room::factory() : null,
            'shift_id'      => $forRoom ? null : Shift::factory(),
            'angel_type_id' => AngelType::factory(),
            'count'         => $this->faker->numberBetween(1, 5),
        ];
    }
}
