<?php

use Engelsystem\Database\DB;

/**
 * Sign up for a shift.
 *
 * @return string
 */
function shift_entry_add_controller()
{
    global $privileges, $user;

    $request = request();
    $shift_id = 0;
    if ($request->has('shift_id') && preg_match('/^\d+$/', $request->input('shift_id'))) {
        $shift_id = $request->input('shift_id');
    } else {
        redirect(page_link_to('user_shifts'));
    }

    // Locations laden
    $rooms = Rooms();
    $room_array = [];
    foreach ($rooms as $room) {
        $room_array[$room['RID']] = $room['Name'];
    }

    $shift = Shift($shift_id);
    if ($shift == null) {
        redirect(page_link_to('user_shifts'));
    }
    $shift['Name'] = $room_array[$shift['RID']];

    $type_id = null;
    if ($request->has('type_id') && preg_match('/^\d+$/', $request->input('type_id'))) {
        $type_id = $request->input('type_id');
    }

    if (in_array('user_shifts_admin', $privileges) || in_array('shiftentry_edit_angeltype_supporter', $privileges)) {
        if($type_id == null) {
            // If no angeltype id is given, then select first existing angeltype.
            $needed_angeltypes = NeededAngelTypes_by_shift($shift_id);
            if(count($needed_angeltypes) > 0) {
                $type_id = $needed_angeltypes[0]['id'];
            }
        }
        $type = AngelType($type_id);
    } else {
        // TODO: Move queries to model
        $type = DB::selectOne('
            SELECT *
            FROM `UserAngelTypes`
            JOIN `AngelTypes` ON (`UserAngelTypes`.`angeltype_id` = `AngelTypes`.`id`)
            WHERE `AngelTypes`.`id` = ?
            AND (
                `AngelTypes`.`restricted` = 0
                OR (
                    `UserAngelTypes`.`user_id` = ?
                    AND NOT `UserAngelTypes`.`confirm_user_id` IS NULL
                )
            )
        ', [$type_id, $user['UID']]);
    }

    if (empty($type)) {
        redirect(page_link_to('user_shifts'));
    }

    if (
        $request->has('user_id')
        && preg_match('/^\d+$/', $request->input('user_id'))
        && (
            in_array('user_shifts_admin', $privileges)
            || in_array('shiftentry_edit_angeltype_supporter', $privileges)
        )
    ) {
        $user_id = $request->input('user_id');
    } else {
        $user_id = $user['UID'];
    }

    $needed_angeltype = NeededAngeltype_by_Shift_and_Angeltype($shift, $type);
    $shift_entries = ShiftEntries_by_shift_and_angeltype($shift['SID'], $type['id']);

    $shift_signup_allowed = Shift_signup_allowed(
        User($user_id),
        $shift,
        $type,
        null,
        null,
        $needed_angeltype,
        $shift_entries
    );
    if (!$shift_signup_allowed->isSignupAllowed()) {
        error(_('You are not allowed to sign up for this shift. Maybe shift is full or already running.'));
        redirect(shift_link($shift));
    }

    if ($request->has('submit')) {
        $selected_type_id = $type_id;
        if (in_array('user_shifts_admin', $privileges) || in_array('shiftentry_edit_angeltype_supporter',
                $privileges)
        ) {

            if (count(DB::select('SELECT `UID` FROM `User` WHERE `UID`=? LIMIT 1', [$user_id])) == 0) {
                redirect(page_link_to('user_shifts'));
            }

            if (
                $request->has('angeltype_id')
                && test_request_int('angeltype_id')
                && count(DB::select(
                    'SELECT `id` FROM `AngelTypes` WHERE `id`=? LIMIT 1',
                    [$request->input('angeltype_id')]
                )) > 0
            ) {
                $selected_type_id = $request->input('angeltype_id');
            }
        }

        if (count(DB::select(
            'SELECT `id` FROM `ShiftEntry` WHERE `SID`= ? AND `UID` = ?',
            [$shift['SID'], $user_id]))
        ) {
            return error('This angel does already have an entry for this shift.', true);
        }

        $freeloaded = isset($shift['freeloaded']) ? $shift['freeloaded'] : false;
        $freeload_comment = isset($shift['freeload_comment']) ? $shift['freeload_comment'] : '';
        if (in_array('user_shifts_admin', $privileges)) {
            $freeloaded = $request->has('freeloaded');
            $freeload_comment = strip_request_item_nl('freeload_comment');
        }

        $comment = strip_request_item_nl('comment');
        ShiftEntry_create([
            'SID'              => $shift_id,
            'TID'              => $selected_type_id,
            'UID'              => $user_id,
            'Comment'          => $comment,
            'freeloaded'       => $freeloaded,
            'freeload_comment' => $freeload_comment
        ]);

        if (
            $type['restricted'] == 0
            && count(DB::select('
              SELECT `UserAngelTypes`.`id` FROM `UserAngelTypes`
              INNER JOIN `AngelTypes` ON `AngelTypes`.`id` = `UserAngelTypes`.`angeltype_id`
              WHERE `angeltype_id` = ?
              AND `user_id` = ?
            ', [$selected_type_id, $user_id])) == 0
        ) {
            DB::insert(
                'INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`) VALUES (?, ?)',
                [$user_id, $selected_type_id]
            );
        }

        $user_source = User($user_id);
        engelsystem_log(
            'User ' . User_Nick_render($user_source)
            . ' signed up for shift ' . $shift['name']
            . ' from ' . date('Y-m-d H:i', $shift['start'])
            . ' to ' . date('Y-m-d H:i', $shift['end'])
        );
        success(_('You are subscribed. Thank you!') . ' <a href="' . page_link_to('user_myshifts') . '">' . _('My shifts') . ' &raquo;</a>');
        redirect(shift_link($shift));
    }

    $angeltype_select = '';
    if (in_array('user_shifts_admin', $privileges)) {
        $users = DB::select('
            SELECT *,
            (
                SELECT count(*)
                FROM `ShiftEntry`
                WHERE `freeloaded`=1
                AND `ShiftEntry`.`UID`=`User`.`UID`
            ) AS `freeloaded`
            FROM `User`
            ORDER BY `Nick`
        ');
        $users_select = [];
        foreach ($users as $usr) {
            $users_select[$usr['UID']] = $usr['Nick'] . ($usr['freeloaded'] == 0 ? '' : ' (' . _('Freeloader') . ')');
        }
        $user_text = html_select_key('user_id', 'user_id', $users_select, $user['UID']);

        $angeltypes_source = DB::select('SELECT `id`, `name` FROM `AngelTypes` ORDER BY `name`');
        $angeltypes = [];
        foreach ($angeltypes_source as $angeltype) {
            $angeltypes[$angeltype['id']] = $angeltype['name'];
        }
        $angeltype_select = html_select_key('angeltype_id', 'angeltype_id', $angeltypes, $type['id']);
    } elseif (in_array('shiftentry_edit_angeltype_supporter', $privileges) && User_is_AngelType_supporter($user, $type)) {
        $users = Users_by_angeltype($type);
        $users_select = [];
        foreach ($users as $usr) {
            if (!$type['restricted'] || $usr['confirm_user_id'] != null) {
                $users_select[$usr['UID']] = $usr['Nick'];
            }
        }
        $user_text = html_select_key('user_id', 'user_id', $users_select, $user['UID']);

        $angeltypes_source = User_angeltypes($user);
        $angeltypes = [];
        foreach ($angeltypes_source as $angeltype) {
            if ($angeltype['supporter']) {
                $angeltypes[$angeltype['id']] = $angeltype['name'];
            }
            $angeltype_select = html_select_key('angeltype_id', 'angeltype_id', $angeltypes, $type['id']);
        }
    } else {
        $user_text = User_Nick_render($user);
        $angeltype_select = $type['name'];
    }

    return ShiftEntry_edit_view(
        $user_text,
        date('Y-m-d H:i', $shift['start'])
        . ' &ndash; '
        . date('Y-m-d H:i', $shift['end'])
        . ' (' . shift_length($shift) . ')',
        $shift['Name'],
        $shift['name'],
        $angeltype_select, '',
        false,
        null,
        in_array('user_shifts_admin', $privileges)
    );
}

/**
 * Remove somebody from a shift.
 */
function shift_entry_delete_controller()
{
    global $privileges, $user;
    $request = request();

    if (!$request->has('entry_id') || !test_request_int('entry_id')) {
        redirect(page_link_to('user_shifts'));
    }
    $entry_id = $request->input('entry_id');

    $shift_entry_source = DB::selectOne('
        SELECT
            `User`.`Nick`,
            `User`.`Gekommen`,
            `ShiftEntry`.`Comment`,
            `ShiftEntry`.`UID`,
            `ShiftTypes`.`name`,
            `Shifts`.*,
            `Room`.`Name`,
            `AngelTypes`.`name` AS `angel_type`,
            `AngelTypes`.`id` AS `angeltype_id`
        FROM `ShiftEntry`
        JOIN `User` ON (`User`.`UID`=`ShiftEntry`.`UID`)
        JOIN `AngelTypes` ON (`ShiftEntry`.`TID` = `AngelTypes`.`id`)
        JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`)
        JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
        JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`)
        WHERE `ShiftEntry`.`id`=?',
        [$entry_id]
    );
    if (!empty($shift_entry_source)) {
        if (!in_array('user_shifts_admin', $privileges) && (!in_array('shiftentry_edit_angeltype_supporter',
                    $privileges) || !User_is_AngelType_supporter($user, AngelType($shift_entry_source['angeltype_id'])))
        ) {
            redirect(page_link_to('user_shifts'));
        }

        ShiftEntry_delete($entry_id);

        engelsystem_log(
            'Deleted ' . User_Nick_render($shift_entry_source) . '\'s shift: ' . $shift_entry_source['name']
            . ' at ' . $shift_entry_source['Name']
            . ' from ' . date('Y-m-d H:i', $shift_entry_source['start'])
            . ' to ' . date('Y-m-d H:i', $shift_entry_source['end'])
            . ' as ' . $shift_entry_source['angel_type']
        );
        success(_('Shift entry deleted.'));
    } else {
        error(_('Entry not found.'));
    }

    redirect(shift_link($shift_entry_source));
}
