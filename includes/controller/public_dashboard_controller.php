<?php
use Engelsystem\Database\Db;

/**
 * Loads all data for the public dashboard
 */
function public_dashboard_controller()
{
    $stats = [];
    
    $now = time();
    $in3hours = $now + 3 * 60 * 60;
    $result = Db::selectOne("
        SELECT SUM(
            (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`)
            - (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
            ) as `count`
        FROM `Shifts` 
        WHERE ((`end` > ? AND `end` < ?) OR (`start` > ? AND `start` < ?))", [
        $now,
        $in3hours,
        $now,
        $in3hours
    ]);
    $stats['needed-3-hours'] = $result['count'];
    
    $night_start = parse_date('Y-m-d H:i', date('Y-m-d', time() + 12 * 60 * 60) . ' 02:00');
    $night_end = $night_start + 6 * 60 * 60;
    $result = Db::selectOne("
        SELECT SUM(
            (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`)
            - (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
            ) as `count`
        FROM `Shifts`
        WHERE ((`end` > ? AND `end` < ?) OR (`start` > ? AND `start` < ?))", [
        $night_start,
        $night_end,
        $night_start,
        $night_end
    ]);
    $stats['needed-night'] = $result['count'];
    
    $result = Db::selectOne("
        SELECT SUM(
            (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
            ) as `count`
        FROM `Shifts`
        WHERE (`end` >= ? AND `start` <= ?)", [
        time(),
        time()
    ]);
    $stats['angels-working'] = $result['count'];
    
    $result = Db::selectOne("
        SELECT ROUND(SUM(
            (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
            * (`Shifts`.`end` - `Shifts`.`start`)/3600
        )) as `count`
        FROM `Shifts`
        WHERE `end` >= ?", [
        time()
    ]);
    $stats['hours-to-work'] = $result['count'];
    
    return [
        'Engelsystem Public Dashboard',
        public_dashboard_view($stats)
    ];
}
?>