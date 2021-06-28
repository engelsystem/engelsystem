<?php

namespace Database\Factories\Engelsystem\Models\User;

use Carbon\Carbon;
use Engelsystem\Models\User\State;
use Illuminate\Database\Eloquent\Factories\Factory;

class StateFactory extends Factory
{
    /** @var string */
    protected $model = State::class;

    /**
     * @return array
     */
    public function definition()
    {
        $arrival = $this->faker->optional()->dateTimeThisMonth();

        return [
            'arrived'      => (bool)$arrival,
            'arrival_date' => $arrival ? Carbon::instance($arrival) : null,
            'active'       => $this->faker->boolean(.3),
            'force_active' => $this->faker->boolean(.1),
            'got_shirt'    => $this->faker->boolean(),
            'got_voucher'  => $this->faker->numberBetween(0, 10),
        ];
    }

    /**
     * Indicate that the user is arrived
     *
     * @return self
     */
    public function arrived()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'arrived'      => true,
                    'arrival_date' => Carbon::instance($this->faker->dateTimeThisMonth()),
                ];
            }
        );
    }
}
