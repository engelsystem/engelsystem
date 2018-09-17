<?php

use Engelsystem\Database\Db;

/**
 * Returns the number of angels currently working.
 *
 * @return int|string
 */
function stats_currently_working()
{
    $result = Db::selectOne("
        SELECT SUM(
            (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
            ) AS `count`
        FROM `Shifts`
        WHERE (`end` >= ? AND `start` <= ?)", [
        time(),
        time()
    ]);

    if (empty($result['count'])) {
        return '-';
    }

    return $result['count'];
}

/**
 * Return the number of hours still to work.
 *
 * @return int|string
 */
function stats_hours_to_work()
{
    $result = Db::selectOne("
        SELECT ROUND(SUM(`count`)) AS `count` FROM (
            SELECT 
                (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`)
                * (`Shifts`.`end` - `Shifts`.`start`)/3600 AS `count`
            FROM `Shifts`
            WHERE `end` >= ?
            AND `Shifts`.`PSID` IS NULL
        
            UNION ALL
        
            SELECT
                (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`room_id`=`Shifts`.`RID`)
                * (`Shifts`.`end` - `Shifts`.`start`)/3600 AS `count`
            FROM `Shifts`
            WHERE `end` >= ?
            AND NOT `Shifts`.`PSID` IS NULL
        ) AS `tmp`
        ", [
        time(),
        time()
    ]);
    if (empty($result['count'])) {
        return '-';
    }
    return $result['count'];
}

/**
 * Returns the number of needed angels in the next 3 hours
 *
 * @return int|string
 */
function stats_angels_needed_three_hours()
{
    $now = time();
    $in3hours = $now + 3 * 60 * 60;
    $result = Db::selectOne("
        SELECT SUM(`count`) AS `count` FROM (
            SELECT
                GREATEST(0,
                    (
                    SELECT SUM(`count`) 
                    FROM `NeededAngelTypes` 
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`NeededAngelTypes`.`angel_type_id` 
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE 
                        AND `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
                    ) - (
                    SELECT COUNT(*) FROM `ShiftEntry` 
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`ShiftEntry`.`TID` 
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE 
                        AND `ShiftEntry`.`SID`=`Shifts`.`SID` 
                        AND `freeloaded`=0
                    )
                )
                AS `count`
            FROM `Shifts`
            WHERE `end` > ? AND `start` < ?
            AND `Shifts`.`PSID` IS NULL
        
            UNION ALL
        
            SELECT
                GREATEST(0,
                    (
                    SELECT SUM(`count`) 
                    FROM `NeededAngelTypes` 
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`NeededAngelTypes`.`angel_type_id` 
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE 
                        AND `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
                    ) - (
                    SELECT COUNT(*) FROM `ShiftEntry` 
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`ShiftEntry`.`TID` 
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE 
                        AND `ShiftEntry`.`SID`=`Shifts`.`SID` 
                        AND `freeloaded`=0
                    )
                )
                AS `count`
            FROM `Shifts`
            WHERE `end` > ? AND `start` < ?
            AND NOT `Shifts`.`PSID` IS NULL
        ) AS `tmp`", [
        $now,
        $in3hours,
        $now,
        $in3hours
    ]);
    if (empty($result['count'])) {
        return '-';
    }
    return $result['count'];
}

/**
 * Returns the number of needed angels for nightshifts (see config)
 *
 * @return int|string
 */
function stats_angels_needed_for_nightshifts()
{
    $nightShiftsConfig = config('night_shifts');
    $nightStartTime = $nightShiftsConfig['start'];
    $nightEndTime = $nightShiftsConfig['end'];

    $night_start = parse_date(
        'Y-m-d H:i',
        date('Y-m-d', time() + 12 * 60 * 60) . ' ' . $nightStartTime . ':00'
    );
    $night_end = $night_start + ($nightEndTime - $nightStartTime) * 60 * 60;
    $result = Db::selectOne("
        SELECT SUM(`count`) AS `count` FROM (
            SELECT
                GREATEST(0,
                    (
                    SELECT SUM(`count`) 
                    FROM `NeededAngelTypes` 
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`NeededAngelTypes`.`angel_type_id` 
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE 
                        AND `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
                    ) - (
                    SELECT COUNT(*) FROM `ShiftEntry` 
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`ShiftEntry`.`TID` 
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE 
                        AND `ShiftEntry`.`SID`=`Shifts`.`SID` 
                        AND `freeloaded`=0
                    )
                )
                AS `count`
            FROM `Shifts`
            WHERE `end` > ? AND `start` < ?
            AND `Shifts`.`PSID` IS NULL
        
            UNION ALL
        
            SELECT
                GREATEST(0,
                    (
                    SELECT SUM(`count`) 
                    FROM `NeededAngelTypes` 
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`NeededAngelTypes`.`angel_type_id` 
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE 
                        AND `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
                    ) - (
                    SELECT COUNT(*) FROM `ShiftEntry` 
                    JOIN `AngelTypes` ON `AngelTypes`.`id`=`ShiftEntry`.`TID` 
                    WHERE `AngelTypes`.`show_on_dashboard`=TRUE 
                        AND `ShiftEntry`.`SID`=`Shifts`.`SID` 
                        AND `freeloaded`=0
                    )
                )
                AS `count`
            FROM `Shifts`
            WHERE `end` > ? AND `start` < ?
            AND NOT `Shifts`.`PSID` IS NULL
        ) AS `tmp`", [
        $night_start,
        $night_end,
        $night_start,
        $night_end
    ]);
    if (empty($result['count'])) {
        return '-';
    }
    return $result['count'];
}
