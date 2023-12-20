<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class NeededAngelTypeFactory extends Factory
{
    /** @var string */
    protected $model = NeededAngelType::class; // phpcs:ignore

    public function definition(): array
    {
        $type = $this->faker->numberBetween(0, 2);

        return [
            'location_id'   => $type == 0 ? Location::factory() : null,
            'shift_id'      => $type == 1 ? null : Shift::factory(),
            'shift_type_id' => $type == 2 ? null : Shift::factory(),
            'angel_type_id' => AngelType::factory(),
            'count'         => $this->faker->numberBetween(1, 5),
        ];
    }
}
