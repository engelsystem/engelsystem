<?php

use Engelsystem\Database\DB;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;

function guest_stats()
{
    $apiKey = config('api_key');
    $request = request();

    if ($request->has('api_key')) {
        if (!empty($apiKey) && $request->input('api_key') == $apiKey) {
            $stats = [];

            $stats['user_count'] = User::all()->count();
            $stats['arrived_user_count'] = State::whereArrived(true)->count();

            $done_shifts_seconds = DB::selectOne('
                SELECT SUM(`Shifts`.`end` - `Shifts`.`start`)
                FROM `ShiftEntry`
                JOIN `Shifts` USING (`SID`)
                WHERE `Shifts`.`end` < UNIX_TIMESTAMP()
            ');
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
