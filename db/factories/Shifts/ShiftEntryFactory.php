<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftEntryFactory extends Factory
{
    /** @var string */
    protected $model = ShiftEntry::class; // phpcs:ignore

    public function definition(): array
    {
        $freeloaded = $this->faker->optional(.01, false)->boolean();

        return [
            'shift_id'           => Shift::factory(),
            'angel_type_id'      => AngelType::factory(),
            'user_id'            => User::factory(),
            'user_comment'       => $this->faker->optional(.05, '')->text(),
            'freeloaded'         => $freeloaded,
            'freeloaded_comment' => $freeloaded ? $this->faker->text() : '',
        ];
    }
}
