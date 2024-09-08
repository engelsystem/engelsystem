<?php

declare(strict_types=1);

namespace Database\Factories\Engelsystem\Models;

use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Psr\Log\LogLevel;

class LogEntryFactory extends Factory
{
    /** @var string */
    protected $model = LogEntry::class; // phpcs:ignore

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'level' => $this->faker->randomElement([
                    LogLevel::EMERGENCY,
                    LogLevel::ALERT,
                    LogLevel::CRITICAL,
                    LogLevel::ERROR,
                    LogLevel::WARNING,
                    LogLevel::NOTICE,
                    LogLevel::INFO,
                    LogLevel::DEBUG,
                ]),
            'message' => $this->faker->text(),
            'created_at' => new Carbon(),
        ];
    }
}
