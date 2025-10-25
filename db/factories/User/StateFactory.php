<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\User;

use Carbon\Carbon;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StateFactory extends Factory
{
    /** @var string */
    protected $model = State::class; // phpcs:ignore

    public function definition(): array
    {
        $arrival = $this->faker->optional()->dateTimeThisMonth();
        $force_active_by = $this->faker->optional(.1)->passthrough(User::factory());
        $force_food_by = $this->faker->optional(.1)->passthrough(User::factory());
        $got_goodie_by = $this->faker->optional(.3)->passthrough(User::factory());

        return [
            'user_id'      => User::factory(),
            'arrival_date' => $arrival ? Carbon::instance($arrival) : null,
            'user_info'    => $this->faker->optional(.1)->text(),
            'active'       => $this->faker->boolean(.3),
            'force_active_by' => $force_active_by,
            'force_food_by' => $force_food_by,
            'got_goodie_by'   => $got_goodie_by,
            'got_voucher'  => $this->faker->numberBetween(0, 10),
        ];
    }

    /**
     * Indicate that the user is arrived
     */
    public function arrived(): self
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'arrival_date' => Carbon::instance($this->faker->dateTimeThisMonth()),
                ];
            }
        );
    }
}
