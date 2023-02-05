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
                FROM `shift_entries`
                WHERE `shift_entries`.`shift_id`=`shifts`.`id`
                AND `freeloaded`=0
                ' . ($filter ? 'AND shift_entries.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
            )) AS `count`
        FROM `shifts`
        WHERE (`end` >=  NOW() AND `start` <=  NOW())
        ' . ($filter ? 'AND shifts.room_id IN (' . implode(',', $filter->getRooms()) . ')' : '')
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
                (SELECT SUM(`count`) FROM `needed_angel_types` WHERE `needed_angel_types`.`shift_id`=`shifts`.`id`' . ($filter ? ' AND needed_angel_types.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
                * TIMESTAMPDIFF(MINUTE, `shifts`.`start`, `shifts`.`end`) / 60 AS `count`
            FROM `shifts`
            LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
            WHERE `shifts`.`end` >= NOW()
            AND s.shift_id IS NULL
            ' . ($filter ? 'AND shifts.room_id IN (' . implode(',', $filter->getRooms()) . ')' : '') . '

            UNION ALL

            SELECT
                (SELECT SUM(`count`) FROM `needed_angel_types` WHERE `needed_angel_types`.`room_id`=`shifts`.`room_id`' . ($filter ? ' AND needed_angel_types.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
                * TIMESTAMPDIFF(MINUTE, `shifts`.`start`, `shifts`.`end`) / 60 AS `count`
            FROM `shifts`
            LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
            WHERE shifts.`end` >=  NOW()
            AND NOT s.shift_id IS NULL
            ' . ($filter ? 'AND shifts.room_id IN (' . implode(',', $filter->getRooms()) . ')' : '') . '
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
                    SELECT SUM(needed_angel_types.`count`)
                    FROM `needed_angel_types`
                    JOIN `angel_types` ON `angel_types`.`id`=`needed_angel_types`.`angel_type_id`
                    WHERE `angel_types`.`show_on_dashboard`=TRUE
                        AND `needed_angel_types`.`shift_id`=`shifts`.`id`
                        ' . ($filter ? 'AND needed_angel_types.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    ) - (
                    SELECT COUNT(*) FROM `shift_entries`
                    JOIN `angel_types` ON `angel_types`.`id`=`shift_entries`.`angel_type_id`
                    WHERE `angel_types`.`show_on_dashboard`=TRUE
                        AND `shift_entries`.`shift_id`=`shifts`.`id`
                        AND `freeloaded`=0
                        ' . ($filter ? 'AND shift_entries.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    )
                )
                AS `count`
            FROM `shifts`
            LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
            WHERE shifts.`end` > NOW() AND shifts.`start` < ?
            AND s.shift_id IS NULL
            ' . ($filter ? 'AND shifts.room_id IN (' . implode(',', $filter->getRooms()) . ')' : '') . '

            UNION ALL

            SELECT
                GREATEST(0,
                    (
                    SELECT SUM(needed_angel_types.`count`)
                    FROM `needed_angel_types`
                    JOIN `angel_types` ON `angel_types`.`id`=`needed_angel_types`.`angel_type_id`
                    WHERE `angel_types`.`show_on_dashboard`=TRUE
                        AND `needed_angel_types`.`room_id`=`shifts`.`room_id`
                        ' . ($filter ? 'AND needed_angel_types.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    ) - (
                    SELECT COUNT(*)
                    FROM `shift_entries`
                    JOIN `angel_types` ON `angel_types`.`id`=`shift_entries`.`angel_type_id`
                    WHERE `angel_types`.`show_on_dashboard`=TRUE
                        AND `shift_entries`.`shift_id`=`shifts`.`id`
                        AND `freeloaded`=0
                        ' . ($filter ? 'AND shift_entries.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    )
                )
                AS `count`
            FROM `shifts`
            LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
            WHERE `end` > NOW() AND `start` < ?
            AND NOT s.shift_id IS NULL
            ' . ($filter ? 'AND shifts.room_id IN (' . implode(',', $filter->getRooms()) . ')' : '') . '
        ) AS `tmp`', [
        $in3hours,
        $in3hours,
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
                    SELECT SUM(needed_angel_types.`count`)
                    FROM `needed_angel_types`
                    JOIN `angel_types` ON `angel_types`.`id`=`needed_angel_types`.`angel_type_id`
                    WHERE `angel_types`.`show_on_dashboard`=TRUE
                        AND `needed_angel_types`.`shift_id`=`shifts`.`id`
                        ' . ($filter ? 'AND needed_angel_types.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    ) - (
                    SELECT COUNT(*) FROM `shift_entries`
                    JOIN `angel_types` ON `angel_types`.`id`=`shift_entries`.`angel_type_id`
                    WHERE `angel_types`.`show_on_dashboard`=TRUE
                        AND `shift_entries`.`shift_id`=`shifts`.`id`
                        AND shift_entries.`freeloaded`=0
                        ' . ($filter ? 'AND shift_entries.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    )
                )
                AS `count`
            FROM `shifts`
            LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
            WHERE shifts.`end` > ? AND shifts.`start` < ?
            AND s.shift_id IS NULL
            ' . ($filter ? 'AND shifts.room_id IN (' . implode(',', $filter->getRooms()) . ')' : '') . '

            UNION ALL

            SELECT
                GREATEST(0,
                    (
                    SELECT SUM(needed_angel_types.`count`)
                    FROM `needed_angel_types`
                    JOIN `angel_types` ON `angel_types`.`id`=`needed_angel_types`.`angel_type_id`
                    WHERE `angel_types`.`show_on_dashboard`=TRUE
                        AND `needed_angel_types`.`room_id`=`shifts`.`room_id`
                        ' . ($filter ? 'AND angel_types.id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    ) - (
                    SELECT COUNT(*) FROM `shift_entries`
                    JOIN `angel_types` ON `angel_types`.`id`=`shift_entries`.`angel_type_id`
                    WHERE `angel_types`.`show_on_dashboard`=TRUE
                        AND `shift_entries`.`shift_id`=`shifts`.`id`
                        AND `freeloaded`=0
                        ' . ($filter ? 'AND shift_entries.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . '
                    )
                )
                AS `count`
            FROM `shifts`
            LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
            WHERE `end` > ? AND `start` < ?
            AND NOT s.shift_id IS NULL
            ' . ($filter ? 'AND shifts.room_id IN (' . implode(',', $filter->getRooms()) . ')' : '') . '
        ) AS `tmp`', [
        $night_start,
        $night_end,
        $night_start,
        $night_end,
    ]);

    return $result['count'] ?: '-';
}
