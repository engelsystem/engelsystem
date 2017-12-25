<?php

use Engelsystem\ShiftSignupState;

/**
 * Route shift entry actions.
 *
 * @return array
 */
function shift_entries_controller()
{
    global $user;

    $action = strip_request_item('action');
    if ($action == null) {
        redirect(user_link($user));
    }

    switch ($action) {
        case 'create':
            return shift_entry_create_controller();
        case 'delete':
            return shift_entry_delete_controller();
    }
}

/**
 * Sign up for a shift.
 *
 * @return array
 */
function shift_entry_create_controller()
{
    global $privileges, $user;
    $request = request();

    if (User_is_freeloader($user)) {
        redirect(page_link_to('user_myshifts'));
    }

    $shift = Shift($request->input('shift_id'));
    if ($shift == null) {
        redirect(user_link($user));
    }

    $angeltype = AngelType($request->input('angeltype_id'));

    if (in_array('user_shifts_admin', $privileges)) {
        return shift_entry_create_controller_admin($shift, $angeltype);
    }

    if ($angeltype == null) {
        redirect(user_link($user));
    }

    if (User_is_AngelType_supporter($user, $angeltype)) {
        return shift_entry_create_controller_supporter($shift, $angeltype);
    }

    return shift_entry_create_controller_user($shift, $angeltype);
}

/**
 * Sign up for a shift.
 * Case: Admin
 *
 * @param array $shift
 * @param array $angeltype
 * @return array
 */
function shift_entry_create_controller_admin($shift, $angeltype)
{
    global $user;
    $request = request();

    $signup_user = $user;
    if ($request->has('user_id')) {
        $signup_user = User($request->input('user_id'));
    }
    if ($signup_user == null) {
        redirect(shift_link($shift));
    }

    $angeltypes = AngelTypes();
    if ($request->has('angeltype_id')) {
        $angeltype = AngelType($request->input('angeltype_id'));
    }
    if ($angeltype == null) {
        if (count($angeltypes) == 0) {
            redirect(shift_link($shift));
        }
        $angeltype = $angeltypes[0];
    }

    if ($request->has('submit')) {
        ShiftEntry_create([
            'SID'              => $shift['SID'],
            'TID'              => $angeltype['id'],
            'UID'              => $signup_user['UID'],
            'Comment'          => '',
            'freeloaded'       => false,
            'freeload_comment' => ''
        ]);

        success(sprintf(_('%s has been subscribed to the shift.'), User_Nick_render($signup_user)));
        redirect(shift_link($shift));
    }

    $users = Users();
    $users_select = [];
    foreach ($users as $u) {
        $users_select[$u['UID']] = $u['Nick'];
    }

    $angeltypes_select = [];
    foreach ($angeltypes as $a) {
        $angeltypes_select[$a['id']] = $a['name'];
    }

    $room = Room($shift['RID']);
    return [
        ShiftEntry_create_title(),
        ShiftEntry_create_view_admin($shift, $room, $angeltype, $angeltypes_select, $signup_user, $users_select)
    ];
}

/**
 * Sign up for a shift.
 * Case: Supporter
 *
 * @param array $shift
 * @param array $angeltype
 * @return array
 */
function shift_entry_create_controller_supporter($shift, $angeltype)
{
    global $user;
    $request = request();

    $signup_user = $user;
    if ($request->has('user_id')) {
        $signup_user = User($request->input('user_id'));
    }
    if (!UserAngelType_exists($signup_user, $angeltype)) {
        error(_('User is not in angeltype.'));
        redirect(shift_link($shift));
    }

    $needed_angeltype = NeededAngeltype_by_Shift_and_Angeltype($shift, $angeltype);
    $shift_entries = ShiftEntries_by_shift_and_angeltype($shift['SID'], $angeltype['id']);
    $shift_signup_state = Shift_signup_allowed(
        $signup_user,
        $shift,
        $angeltype,
        null,
        null,
        $needed_angeltype,
        $shift_entries
    );
    if (!$shift_signup_state->isSignupAllowed()) {
        if ($shift_signup_state->getState() == ShiftSignupState::OCCUPIED) {
            error(_('This shift is already occupied.'));
        }
        redirect(shift_link($shift));
    }

    if ($request->has('submit')) {
        ShiftEntry_create([
            'SID'              => $shift['SID'],
            'TID'              => $angeltype['id'],
            'UID'              => $signup_user['UID'],
            'Comment'          => '',
            'freeloaded'       => false,
            'freeload_comment' => ''
        ]);

        success(sprintf(_('%s has been subscribed to the shift.'), User_Nick_render($signup_user)));
        redirect(shift_link($shift));
    }

    $users = Users_by_angeltype($angeltype);
    $users_select = [];
    foreach ($users as $u) {
        $users_select[$u['UID']] = $u['Nick'];
    }

    $room = Room($shift['RID']);
    return [
        ShiftEntry_create_title(),
        ShiftEntry_create_view_supporter($shift, $room, $angeltype, $signup_user, $users_select)
    ];
}

/**
 * Generates an error message for the given shift signup state.
 *
 * @param ShiftSignupState $shift_signup_state
 */
function shift_entry_error_message(ShiftSignupState $shift_signup_state)
{
    if ($shift_signup_state->getState() == ShiftSignupState::ANGELTYPE) {
        error(_('You need be accepted member of the angeltype.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::COLLIDES) {
        error(_('This shift collides with one of your shifts.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::OCCUPIED) {
        error(_('This shift is already occupied.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::SHIFT_ENDED) {
        error(_('This shift ended already.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::NOT_ARRIVED) {
        error(_('You are not marked as arrived.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::SIGNED_UP) {
        error(_('You are signed up for this shift.'));
    }
}

/**
 * Sign up for a shift.
 * Case: User
 *
 * @param array $shift
 * @param array $angeltype
 * @return array
 */
function shift_entry_create_controller_user($shift, $angeltype)
{
    global $user;
    $request = request();

    $signup_user = $user;
    $needed_angeltype = NeededAngeltype_by_Shift_and_Angeltype($shift, $angeltype);
    $shift_entries = ShiftEntries_by_shift_and_angeltype($shift['SID'], $angeltype['id']);
    $shift_signup_state = Shift_signup_allowed(
        $signup_user,
        $shift,
        $angeltype,
        null,
        null,
        $needed_angeltype,
        $shift_entries
    );
    if (!$shift_signup_state->isSignupAllowed()) {
        shift_entry_error_message($shift_signup_state);
        redirect(shift_link($shift));
    }

    $comment = '';
    if ($request->has('submit')) {
        $comment = strip_request_item_nl('comment');
        ShiftEntry_create([
            'SID'              => $shift['SID'],
            'TID'              => $angeltype['id'],
            'UID'              => $signup_user['UID'],
            'Comment'          => $comment,
            'freeloaded'       => false,
            'freeload_comment' => ''
        ]);

        if ($angeltype['restricted'] == false && !UserAngelType_exists($signup_user, $angeltype)) {
            UserAngelType_create($signup_user, $angeltype);
        }

        success(_('You are subscribed. Thank you!'));
        redirect(shift_link($shift));
    }

    $room = Room($shift['RID']);
    return [
        ShiftEntry_create_title(),
        ShiftEntry_create_view_user($shift, $room, $angeltype, $comment)
    ];
}

/**
 * Link to create a shift entry.
 *
 * @param array $shift
 * @param array $angeltype
 * @param array $params
 * @return string URL
 */
function shift_entry_create_link($shift, $angeltype, $params = [])
{
    $params = array_merge([
        'action'       => 'create',
        'shift_id'     => $shift['SID'],
        'angeltype_id' => $angeltype['id']
    ], $params);
    return page_link_to('shift_entries', $params);
}

/**
 * Link to create a shift entry as admin.
 *
 * @param array $shift
 * @param array $params
 * @return string URL
 */
function shift_entry_create_link_admin($shift, $params = [])
{
    $params = array_merge([
        'action'   => 'create',
        'shift_id' => $shift['SID']
    ], $params);
    return page_link_to('shift_entries', $params);
}

/**
 * Load a shift entry from get parameter shift_entry_id.
 *
 * @return array
 */
function shift_entry_load()
{
    $request = request();

    if (!$request->has('shift_entry_id') || !test_request_int('shift_entry_id')) {
        redirect(page_link_to('user_shifts'));
    }
    $shiftEntry = ShiftEntry($request->input('shift_entry_id'));
    if ($shiftEntry == null) {
        error(_('Shift entry not found.'));
        redirect(page_link_to('user_shifts'));
    }

    return $shiftEntry;
}

/**
 * Remove somebody from a shift.
 *
 * @return array
 */
function shift_entry_delete_controller()
{
    global $user;
    $request = request();
    $shiftEntry = shift_entry_load();

    $shift = Shift($shiftEntry['SID']);
    $angeltype = AngelType($shiftEntry['TID']);
    $signout_user = User($shiftEntry['UID']);
    if (! Shift_signout_allowed($shift, $angeltype, $signout_user)) {
        error(_('You are not allowed to remove this shift entry. If necessary, ask your supporter or heaven to do so.'));
        redirect(user_link($signout_user));
    }

    if ($request->has('continue')) {
        ShiftEntry_delete($shiftEntry);
        success(_('Shift entry removed.'));
        redirect(shift_link($shift));
    }

    if ($user['UID'] == $signout_user['UID']) {
        return [
            ShiftEntry_delete_title(),
            ShiftEntry_delete_view($shiftEntry, $shift, $angeltype, $signout_user)
        ];
    }

    return [
        ShiftEntry_delete_title(),
        ShiftEntry_delete_view_admin($shiftEntry, $shift, $angeltype, $signout_user)
    ];
}

/**
 * Link to delete a shift entry.
 *
 * @param array $shiftEntry
 * @param array $params
 * @return string URL
 */
function shift_entry_delete_link($shiftEntry, $params = [])
{
    $params = array_merge([
        'action'         => 'delete',
        'shift_entry_id' => $shiftEntry['id']
    ], $params);
    return page_link_to('shift_entries', $params);
}
