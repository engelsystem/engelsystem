<?php

use Engelsystem\Database\Db;
use Engelsystem\ShiftsFilter;

/**
 * Returns the number of angels currently working.
 *
 * @param ShiftsFilter|null $filter
 *
 * @return int|string
 */
function stats_currently_working(ShiftsFilter $filter = null)
{
    $result = Db::selectOne(
        '
        SELECT SUM((
                SELECT COUNT(*)
                FROM `ShiftEntry`
                WHERE `ShiftEntry`.`SID`=`Shifts`.`SID`
                AND `freeloaded`=0
                ' . ($filter ? 'AND ShiftEntry.TID IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
            )) AS `count`
        FROM `Shifts`
        WHERE (`end` >= UNIX_TIMESTAMP() AND `start` <= UNIX_TIMESTAMP())
        '. ($filter ? 'AND Shifts.RID IN (' . implode(',', $filter->getRooms()) . ')' : '')
    );

    return $result['count'] ?: '-';
}

/**
 * Return the number of hours still to work.
 *
 * @param ShiftsFilter|null $filter
 *
 * @return int|string
 */
function stats_hours_to_work(ShiftsFilter $filter = null)
{
    $result = Db::selectOne(
        '
        SELECT ROUND(SUM(`count`)) AS `count` FROM (
            SELECT
                (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`' . ($filter ? ' AND NeededAngelTypes.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
                * (`Shifts`.`end` - `Shifts`.`start`)/3600 AS `count`
            FROM `Shifts`
            LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
            WHERE `end` >= UNIX_TIMESTAMP()
            AND s.shift_id IS NULL
            '. ($filter ? 'AND Shifts.RID IN (' . implode(',', $filter->getRooms()) . ')' : '') . '

            UNION ALL

            SELECT
                (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`room_id`=`Shifts`.`RID`' . ($filter ? ' AND NeededAngelTypes.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
                * (`Shifts`.`end` - `Shifts`.`start`)/3600 AS `count`
            FROM `Shifts`
            LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
            WHERE `end` >= UNIX_TIMESTAMP()
            AND NOT s.shift_id IS NULL
            '. ($filter ? 'AND Shifts.RID IN (' . implode(',', $filter->getRooms()) . ')' : '') . '
        ) AS `tmp`
        '
    );

    return $result['count'] ?: '-';
}

/**
 * Returns the number of needed angels in the next 3 hours
 *
 * @param ShiftsFilter|null $filter
 *
 * @return int|string
 */
function stats_angels_needed_three_hours(ShiftsFilter $filter = null)
{
    $in3hours = time() + 3 * 60 * 60;
    $result = Db::selectOne('
        SELECT SUM(`count`) AS `count` FROM (
            SELECT
                GREATEST(0,
                    (
                    SELECT SUM(`count`)
                    FROM `NeededAngelTypes`
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`NeededAngelTypes`.`angel_type_id`
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE
                        AND `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
                        ' . ($filter ? 'AND NeededAngelTypes.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    ) - (
                    SELECT COUNT(*) FROM `ShiftEntry`
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`ShiftEntry`.`TID`
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE
                        AND `ShiftEntry`.`SID`=`Shifts`.`SID`
                        AND `freeloaded`=0
                        ' . ($filter ? 'AND ShiftEntry.TID IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    )
                )
                AS `count`
            FROM `Shifts`
            LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
            WHERE `end` > UNIX_TIMESTAMP() AND `start` < ?
            AND s.shift_id IS NULL
            '. ($filter ? 'AND Shifts.RID IN (' . implode(',', $filter->getRooms()) . ')' : '') . '

            UNION ALL

            SELECT
                GREATEST(0,
                    (
                    SELECT SUM(`count`)
                    FROM `NeededAngelTypes`
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`NeededAngelTypes`.`angel_type_id`
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE
                        AND `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
                        ' . ($filter ? 'AND NeededAngelTypes.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    ) - (
                    SELECT COUNT(*) FROM `ShiftEntry`
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`ShiftEntry`.`TID`
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE
                        AND `ShiftEntry`.`SID`=`Shifts`.`SID`
                        AND `freeloaded`=0
                        ' . ($filter ? 'AND ShiftEntry.TID IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    )
                )
                AS `count`
            FROM `Shifts`
            LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
            WHERE `end` > UNIX_TIMESTAMP() AND `start` < ?
            AND NOT s.shift_id IS NULL
            '. ($filter ? 'AND Shifts.RID IN (' . implode(',', $filter->getRooms()) . ')' : '') . '
        ) AS `tmp`', [
        $in3hours,
        $in3hours
    ]);

    return $result['count'] ?: '-';
}

/**
 * Returns the number of needed angels for nightshifts (see config)
 *
 * @param ShiftsFilter|null $filter
 *
 * @return int|string
 */
function stats_angels_needed_for_nightshifts(ShiftsFilter $filter = null)
{
    $nightShiftsConfig = config('night_shifts');
    $nightStartTime = $nightShiftsConfig['start'];
    $nightEndTime = $nightShiftsConfig['end'];

    $night_start = parse_date(
        'Y-m-d H:i',
        date('Y-m-d', time() + 12 * 60 * 60) . ' ' . $nightStartTime . ':00'
    );
    $night_end = $night_start + ($nightEndTime - $nightStartTime) * 60 * 60;
    $result = Db::selectOne('
        SELECT SUM(`count`) AS `count` FROM (
            SELECT
                GREATEST(0,
                    (
                    SELECT SUM(`count`)
                    FROM `NeededAngelTypes`
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`NeededAngelTypes`.`angel_type_id`
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE
                        AND `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
                        ' . ($filter ? 'AND NeededAngelTypes.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    ) - (
                    SELECT COUNT(*) FROM `ShiftEntry`
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`ShiftEntry`.`TID`
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE
                        AND `ShiftEntry`.`SID`=`Shifts`.`SID`
                        AND `freeloaded`=0
                        ' . ($filter ? 'AND ShiftEntry.TID IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    )
                )
                AS `count`
            FROM `Shifts`
            LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
            WHERE `end` > ? AND `start` < ?
            AND s.shift_id IS NULL
            '. ($filter ? 'AND Shifts.RID IN (' . implode(',', $filter->getRooms()) . ')' : '') . '

            UNION ALL

            SELECT
                GREATEST(0,
                    (
                    SELECT SUM(`count`)
                    FROM `NeededAngelTypes`
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`NeededAngelTypes`.`angel_type_id`
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE
                        AND `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
                        ' . ($filter ? 'AND AngelTypes.id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    ) - (
                    SELECT COUNT(*) FROM `ShiftEntry`
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`ShiftEntry`.`TID`
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE
                        AND `ShiftEntry`.`SID`=`Shifts`.`SID`
                        AND `freeloaded`=0
                        ' . ($filter ? 'AND ShiftEntry.TID IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    )
                )
                AS `count`
            FROM `Shifts`
            LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
            WHERE `end` > ? AND `start` < ?
            AND NOT s.shift_id IS NULL
            '. ($filter ? 'AND Shifts.RID IN (' . implode(',', $filter->getRooms()) . ')' : '') . '
        ) AS `tmp`', [
        $night_start,
        $night_end,
        $night_start,
        $night_end
    ]);

    return $result['count'] ?: '-';
}
