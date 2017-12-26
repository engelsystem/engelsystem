<?php
use Engelsystem\Database\DB;
use Engelsystem\ShiftsFilter;

function shifts_browser_title()
{
    return _("Shifts");
}

function user_shifts_browser()
{
    global $user;
    return view_user_shifts_browser();
}

function view_user_shifts_browser()
{
    // get user
    global $user;

    // get user's angeltypes
    $res = DB::select("
        SELECT angeltype_id FROM `UserAngelTypes`
        JOIN AngelTypes ON angeltype_id = AngelTypes.id
        WHERE UserAngelTypes.user_id = ?
        AND AngelTypes.restricted = 0 OR (AngelTypes.restricted = 1 AND UserAngelTypes.confirm_user_id)
        ",
        [
            $user['UID'],
        ]
    );
    $uat = array();
    foreach($res as $r) {
        $uat[] = $r['angeltype_id'];
    }
    $user_angeltypes = implode(',', $uat);

    // translation definitions
    $lang_items = array(
        'Shifts',
        'Time',
        'Fetching data from server...',
        'Importing new objects into browser database.',
        'remaining...',
        'Abort and switch to legacy view',
        'The tasks shown here are influenced by the angeltypes you joined already!',
        'Description of the jobs.',
        'Loading...',
        'Rooms',
        'All',
        'None',
        'Angeltypes',
        'Occupancy',
        'Free',
        'helpers needed',
        'ended',
        'Become',
        'Sign up',
        'Add more angels',
        'No shifts could be found for the selected date.',
    );

    // translate those definitions
    $lang = array();
    foreach($lang_items as $li) {
        $lang[$li] = _($li);
    }

    return page([
        div('col-md-12', [
            view(__DIR__ . '/../../templates/user_shifts_browser.html', [
                'title' => shifts_browser_title(),
                'user_id' => $user['UID'],
                'user_angeltypes' => $user_angeltypes,
                'lang_json' => json_encode($lang),
            ])
        ])
    ]);
}

