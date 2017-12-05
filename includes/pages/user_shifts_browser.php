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

    return page([
        div('col-md-12', [
            view(__DIR__ . '/../../templates/user_shifts_browser.html', [
                'title' => shifts_browser_title(),
                'user_id' => $user['UID'],
                'user_angeltypes' => $user_angeltypes,
            ])
        ])
    ]);
}

