<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Database\Database;
use Engelsystem\Models\User\User;
use Illuminate\Database\Query\Expression;

class Goodie
{
    public static function worklogScoreQuery(): Expression
    {
        $nightShifts = config('night_shifts');

        /** @var Database $db */
        $db = app(Database::class);
        $connection = $db->getConnection();

        if (!$nightShifts['enabled']) {
            return $connection->raw(
                /** @lang MySQL */
                'COALESCE(SUM(`hours`), 0)'
            );
        }

        return $connection->raw(sprintf(
            /** @lang MySQL */
            'COALESCE(SUM(IF(`night_shift`, `hours` * %d, `hours`)), 0)',
            $nightShifts['multiplier']
        ));
    }

    /**
     * Returns the goodie score (number of hours counted for goodie score)
     * Includes only ended shifts
     */
    public static function userScore(User $user): float
    {
        /** @var Database $db */
        $db = app(Database::class);
        $con = $db->getConnection();
        $nightShifts = config('night_shifts');

        $shiftScoreQuery = /** @lang MySQL */
            'SELECT goodie_score(?, ?, ?, ?, ?) / 3600.0 as goodie_score';

        $state = $con->selectOne(
            $shiftScoreQuery,
            [
                $user->id,
                $nightShifts['start'],
                $nightShifts['end'],
                $nightShifts['multiplier'],
                Carbon::now(),
            ]
        );

        $shiftHours = 0;
        if ($state) {
            // @codeCoverageIgnoreStart
            $shiftHours = (float) $state->goodie_score;
            // @codeCoverageIgnoreEnd
        }

        $worklogHours = $user->worklogs()
            ->where('worked_at', '<=', Carbon::Now())
            ->selectRaw(sprintf(
                /** @lang MySQL */
                '%s as `total_hours`',
                self::worklogScoreQuery()->getValue($con->getQueryGrammar())
            ))
            ->value('total_hours');

        return $shiftHours + $worklogHours;
    }
}
