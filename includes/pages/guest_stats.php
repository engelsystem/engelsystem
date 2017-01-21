<?php

use Engelsystem\Database\DB;

function guest_stats()
{
    $apiKey = config('api_key');

    if (isset($_REQUEST['api_key'])) {
        if ($_REQUEST['api_key'] == $apiKey && !empty($apiKey)) {
            $stats = [];

            list($user_count) = DB::select('SELECT count(*) AS `user_count` FROM `User`');
            $stats['user_count'] = $user_count['user_count'];

            list($arrived_user_count) = DB::select('SELECT count(*) AS `user_count` FROM `User` WHERE `Gekommen`=1');
            $stats['arrived_user_count'] = $arrived_user_count['user_count'];

            $done_shifts_seconds = DB::select('
                SELECT SUM(`Shifts`.`end` - `Shifts`.`start`)
                FROM `ShiftEntry`
                JOIN `Shifts` USING (`SID`)
                WHERE `Shifts`.`end` < UNIX_TIMESTAMP()
            ');
            $done_shifts_seconds = array_shift($done_shifts_seconds);
            $done_shifts_seconds = (int)array_shift($done_shifts_seconds);
            $stats['done_work_hours'] = round($done_shifts_seconds / (60 * 60), 0);

            $users_in_action = DB::select('
                SELECT `Shifts`.`start`, `Shifts`.`end`
                FROM `ShiftEntry`
                JOIN `Shifts` ON `Shifts`.`SID`=`ShiftEntry`.`SID`
                WHERE UNIX_TIMESTAMP() BETWEEN `Shifts`.`start` AND `Shifts`.`end`
            ');
            $stats['users_in_action'] = count($users_in_action);

            header('Content-Type: application/json');
            raw_output(json_encode($stats));
            return;
        }
        raw_output(json_encode([
            'error' => 'Wrong api_key.'
        ]));
    }
    raw_output(json_encode([
        'error' => 'Missing parameter api_key.'
    ]));
}
