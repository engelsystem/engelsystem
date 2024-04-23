<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models\User;

use Engelsystem\Models\User\License;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseFactory extends Factory
{
    /** @var string */
    protected $model = License::class; // phpcs:ignore

    public function definition(): array
    {
        $drive_car = $this->faker->boolean(.8);
        $drive_3_5t = $drive_car && $this->faker->boolean(.7);
        $drive_7_5t = $drive_3_5t && $this->faker->boolean();
        $drive_12t = $drive_7_5t && $this->faker->boolean(.3);
        $drive_forklift = ($drive_car && $this->faker->boolean(.1))
            || ($drive_12t && $this->faker->boolean(.7));
        $drive_confirmed = $this->faker->boolean(0.5) && (
            $drive_car
            || $drive_3_5t
            || $drive_7_5t
            || $drive_12t
            || $drive_forklift
        );

        $ifsg_certificate = $this->faker->boolean(0.1);
        $ifsg_certificate_light = $this->faker->boolean(0.5) && !$ifsg_certificate;
        $ifsg_confirmed = $this->faker->boolean(0.5) && ($ifsg_certificate || $ifsg_certificate_light);

        return [
            'user_id'                => User::factory(),
            'has_car'                => $drive_car && $this->faker->boolean(.7),
            'drive_forklift'         => $drive_forklift,
            'drive_car'              => $drive_car,
            'drive_3_5t'             => $drive_3_5t,
            'drive_7_5t'             => $drive_7_5t,
            'drive_12t'              => $drive_12t,
            'drive_confirmed'        => $drive_confirmed,
            'ifsg_certificate'       => $ifsg_certificate,
            'ifsg_certificate_light' => $ifsg_certificate_light,
            'ifsg_confirmed'         => $ifsg_confirmed,
        ];
    }
}
