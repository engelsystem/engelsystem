<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Database\Database;
use Engelsystem\Models\User\User;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\MySqlGrammar;

class Goodie
{
    /**
     * Generates the query to sum night shifts
     *
     * Shifts and shift entries must be available via join
     */
    public static function shiftScoreQuery(): Expression
    {
        $nightShifts = config('night_shifts');

        /** @var Database $db */
        $db = app(Database::class);
        $connection = $db->getConnection();

        if (!$connection->getQueryGrammar() instanceof MySqlGrammar) {
            return $connection->raw('0');
        }

        // @codeCoverageIgnoreStart
        // as sqlite does not support TIMESTAMPDIFF

        if (!$nightShifts['enabled']) {
            return $connection->raw(
                /** @lang MySQL */
                'COALESCE(SUM(TIMESTAMPDIFF(MINUTE, shifts.start, shifts.end) * 60), 0)'
            );
        }

        /* @see \Engelsystem\Models\Shifts\Shift::isNightShift to keep them in sync */
        $query =
            /** @lang MySQL */
            '
                COALESCE(SUM(
                    /* Shift length */
                    TIMESTAMPDIFF(MINUTE, shifts.start, shifts.end) * 60
                    /* Is night shift */
                    * (
                        CASE WHEN
                            /* Starts during night */
                            HOUR(shifts.start) >= %1$d AND HOUR(shifts.start) < %2$d
                            /* Ends during night */
                            OR (
                                HOUR(shifts.end) > %1$d
                                || HOUR(shifts.end) = %1$d AND MINUTE(shifts.end) > 0
                            ) AND HOUR(shifts.end) <= %2$d
                            /* Starts before and ends after night */
                            OR HOUR(shifts.start) <= %1$d AND HOUR(shifts.end) >= %2$d
                        /* Use multiplier */
                        THEN
                            /* Handle freeloading */
                            CASE WHEN `shift_entries`.`freeloaded_by` IS NULL
                            THEN %3$d
                            ELSE -%3$d
                            END
                        ELSE
                            /* Handle freeloading */
                            CASE WHEN `shift_entries`.`freeloaded_by` IS NULL
                            THEN 1
                            ELSE -2
                            END
                        END
                    )
                ), 0)
            ';

        $query = sprintf($query, $nightShifts['start'], $nightShifts['end'], $nightShifts['multiplier']);

        return $connection->raw($query);
        // @codeCoverageIgnoreEnd
    }

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

        $state = $con
            ->query()
            ->from('users')
            ->selectRaw(sprintf(
                /** @lang MySQL */
                'ROUND((%s) / 3600, 2) AS `goodie_score`',
                self::shiftScoreQuery()->getValue($con->getQueryGrammar())
            ))
            ->where('users.id', $user->id)
            ->join('shift_entries', 'users.id', 'shift_entries.user_id')
            ->join('shifts', 'shift_entries.shift_id', 'shifts.id')
            ->where('shifts.end', '<', Carbon::now())
            ->groupBy('users.id')
            ->first();

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
