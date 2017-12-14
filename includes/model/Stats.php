<?php
use Engelsystem\Database\Db;

/**
 * Returns the number of angels currently working.
 */
function stats_currently_working()
{
    $result = Db::selectOne("
        SELECT SUM(
            (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
            ) as `count`
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
 */
function stats_hours_to_work()
{
    $result = Db::selectOne("
        SELECT ROUND(SUM(`count`)) as `count` FROM (
            SELECT 
                (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`)
                * (`Shifts`.`end` - `Shifts`.`start`)/3600 as `count`
            FROM `Shifts`
            WHERE `end` >= ?
            AND `Shifts`.`PSID` IS NULL
        
            UNION
        
            SELECT
                (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`room_id`=`Shifts`.`RID`)
                * (`Shifts`.`end` - `Shifts`.`start`)/3600 as `count`
            FROM `Shifts`
            WHERE `end` >= ?
            AND NOT `Shifts`.`PSID` IS NULL
        ) as `tmp`
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
 */
function stats_angels_needed_three_hours()
{
    $now = time();
    $in3hours = $now + 3 * 60 * 60;
    $result = Db::selectOne("
        SELECT SUM(`count`) as `count` FROM (
            SELECT
                (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`)
                - (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
                as `count`
            FROM `Shifts`
            WHERE `end` > ? AND `start` < ?
            AND `Shifts`.`PSID` IS NULL
        
            UNION
        
            SELECT
                (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`room_id`=`Shifts`.`RID`)
                - (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
                as `count`
            FROM `Shifts`
            WHERE `end` > ? AND `start` < ?
            AND NOT `Shifts`.`PSID` IS NULL
        ) as `tmp`", [
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
 * Returns the number of needed angels for nightshifts (between 2 and 8)
 */
function stats_angels_needed_for_nightshifts()
{
    $night_start = parse_date('Y-m-d H:i', date('Y-m-d', time() + 12 * 60 * 60) . ' 02:00');
    $night_end = $night_start + 6 * 60 * 60;
    $result = Db::selectOne("
        SELECT SUM(`count`) as `count` FROM (
            SELECT
                (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`)
                - (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
                as `count`
            FROM `Shifts`
            WHERE `end` > ? AND `start` < ?
            AND `Shifts`.`PSID` IS NULL
        
            UNION
        
            SELECT
                (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`room_id`=`Shifts`.`RID`)
                - (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
                as `count`
            FROM `Shifts`
            WHERE `end` > ? AND `start` < ?
            AND NOT `Shifts`.`PSID` IS NULL
        ) as `tmp`", [
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

?>