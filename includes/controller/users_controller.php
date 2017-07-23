<?php

use Engelsystem\Database\DB;
use Engelsystem\ShiftCalendarRenderer;
use Engelsystem\ShiftsFilter;

/**
 * Route user actions.
 *
 * @return array
 */
function users_controller()
{
    global $user;
    $request = request();

    if (!isset($user)) {
        redirect(page_link_to(''));
    }

    $action = 'list';
    if ($request->has('action')) {
        $action = $request->input('action');
    }

    switch ($action) {
        case 'view':
            return user_controller();
        case 'delete':
            return user_delete_controller();
        case 'edit_vouchers':
            return user_edit_vouchers_controller();
        case 'list':
        default:
            return users_list_controller();
    }
}

/**
 * Delete a user, requires to enter own password for reasons.
 *
 * @return array
 */
function user_delete_controller()
{
    global $privileges, $user;
    $request = request();

    if ($request->has('user_id')) {
        $user_source = User($request->get('user_id'));
    } else {
        $user_source = $user;
    }

    if (!in_array('admin_user', $privileges)) {
        redirect(page_link_to(''));
    }

    // You cannot delete yourself
    if ($user['UID'] == $user_source['UID']) {
        error(_('You cannot delete yourself.'));
        redirect(user_link($user));
    }

    if ($request->has('submit')) {
        $valid = true;

        if (
        !(
            $request->has('password')
            && verify_password($request->post('password'), $user['Passwort'], $user['UID'])
        )
        ) {
            $valid = false;
            error(_('Your password is incorrect.  Please try it again.'));
        }

        if ($valid) {
            $result = User_delete($user_source['UID']);
            if ($result === false) {
                engelsystem_error('Unable to delete user.');
            }

            mail_user_delete($user_source);
            success(_('User deleted.'));
            engelsystem_log(sprintf('Deleted %s', User_Nick_render($user_source)));

            redirect(users_link());
        }
    }

    return [
        sprintf(_('Delete %s'), $user_source['Nick']),
        User_delete_view($user_source)
    ];
}

/**
 * @return string
 */
function users_link()
{
    return page_link_to('users');
}

/**
 * @param array $user
 * @return string
 */
function user_edit_link($user)
{
    return page_link_to('admin_user') . '&user_id=' . $user['UID'];
}

/**
 * @param array $user
 * @return string
 */
function user_delete_link($user)
{
    return page_link_to('users') . '&action=delete&user_id=' . $user['UID'];
}

/**
 * @param array $user
 * @return string
 */
function user_link($user)
{
    return page_link_to('users') . '&action=view&user_id=' . $user['UID'];
}

/**
 * @return array
 */
function user_edit_vouchers_controller()
{
    global $privileges, $user;
    $request = request();

    if ($request->has('user_id')) {
        $user_source = User($request->input('user_id'));
    } else {
        $user_source = $user;
    }

    if (!in_array('admin_user', $privileges)) {
        redirect(page_link_to(''));
    }

    if ($request->has('submit')) {
        $valid = true;

        $vouchers = '';
        if (
            $request->has('vouchers')
            && test_request_int('vouchers')
            && trim($request->input('vouchers')) >= 0
        ) {
            $vouchers = trim($request->input('vouchers'));
        } else {
            $valid = false;
            error(_('Please enter a valid number of vouchers.'));
        }

        if ($valid) {
            $user_source['got_voucher'] = $vouchers;

            User_update($user_source);

            success(_('Saved the number of vouchers.'));
            engelsystem_log(User_Nick_render($user_source) . ': ' . sprintf('Got %s vouchers',
                    $user_source['got_voucher']));

            redirect(user_link($user_source));
        }
    }

    return [
        sprintf(_('%s\'s vouchers'), $user_source['Nick']),
        User_edit_vouchers_view($user_source)
    ];
}

/**
 * @return array
 */
function user_controller()
{
    global $privileges, $user;
    $request = request();

    $user_source = $user;
    if ($request->has('user_id')) {
        $user_source = User($request->input('user_id'));
        if ($user_source == null) {
            error(_('User not found.'));
            redirect('?');
        }
    }

    $shifts = Shifts_by_user($user_source, in_array('user_shifts_admin', $privileges));
    foreach ($shifts as &$shift) {
        // TODO: Move queries to model
        $shift['needed_angeltypes'] = DB::select('
            SELECT DISTINCT `AngelTypes`.*
            FROM `ShiftEntry`
            JOIN `AngelTypes` ON `ShiftEntry`.`TID`=`AngelTypes`.`id`
            WHERE `ShiftEntry`.`SID` = ?
            ORDER BY `AngelTypes`.`name`
            ',
            [$shift['SID']]
        );
        foreach ($shift['needed_angeltypes'] as &$needed_angeltype) {
            $needed_angeltype['users'] = DB::select('
                  SELECT `ShiftEntry`.`freeloaded`, `User`.*
                  FROM `ShiftEntry`
                  JOIN `User` ON `ShiftEntry`.`UID`=`User`.`UID`
                  WHERE `ShiftEntry`.`SID` = ?
                  AND `ShiftEntry`.`TID` = ?
                ',
                [$shift['SID'], $needed_angeltype['id']]
            );
        }
    }

    if ($user_source['api_key'] == '') {
        User_reset_api_key($user_source, false);
    }

    return [
        $user_source['Nick'],
        User_view(
            $user_source,
            in_array('admin_user', $privileges),
            User_is_freeloader($user_source),
            User_angeltypes($user_source),
            User_groups($user_source),
            $shifts,
            $user['UID'] == $user_source['UID']
        )
    ];
}

/**
 * List all users.
 *
 * @return array
 */
function users_list_controller()
{
    global $privileges;
    $request = request();

    if (!in_array('admin_user', $privileges)) {
        redirect(page_link_to(''));
    }

    $order_by = 'Nick';
    if ($request->has('OrderBy') && in_array($request->input('OrderBy'), User_sortable_columns())) {
        $order_by = $request->input('OrderBy');
    }

    $users = Users($order_by);
    if ($users === false) {
        engelsystem_error('Unable to load users.');
    }

    foreach ($users as &$user) {
        $user['freeloads'] = count(ShiftEntries_freeloaded_by_user($user));
    }

    return [
        _('All users'),
        Users_view(
            $users,
            $order_by,
            User_arrived_count(),
            User_active_count(),
            User_force_active_count(),
            ShiftEntries_freeleaded_count(),
            User_tshirts_count(),
            User_got_voucher_count()
        )
    ];
}

/**
 * Second step of password recovery: set a new password using the token link from email
 *
 * @return string
 */
function user_password_recovery_set_new_controller()
{
    $request = request();
    $user_source = User_by_password_recovery_token($request->input('token'));
    if ($user_source == null) {
        error(_('Token is not correct.'));
        redirect(page_link_to('login'));
    }

    if ($request->has('submit')) {
        $valid = true;

        if (
            $request->has('password')
            && strlen($request->post('password')) >= config('min_password_length')
        ) {
            if ($request->post('password') != $request->post('password2')) {
                $valid = false;
                error(_('Your passwords don\'t match.'));
            }
        } else {
            $valid = false;
            error(_('Your password is to short (please use at least 6 characters).'));
        }

        if ($valid) {
            set_password($user_source['UID'], $request->post('password'));
            success(_('Password saved.'));
            redirect(page_link_to('login'));
        }
    }

    return User_password_set_view();
}

/**
 * First step of password recovery: display a form that asks for your email and send email with recovery link
 *
 * @return string
 */
function user_password_recovery_start_controller()
{
    $request = request();
    if ($request->has('submit')) {
        $valid = true;

        if ($request->has('email') && strlen(strip_request_item('email')) > 0) {
            $email = strip_request_item('email');
            if (check_email($email)) {
                $user_source = User_by_email($email);
                if ($user_source == null) {
                    $valid = false;
                    error(_('E-mail address is not correct.'));
                }
            } else {
                $valid = false;
                error(_('E-mail address is not correct.'));
            }
        } else {
            $valid = false;
            error(_('Please enter your e-mail.'));
        }

        if ($valid) {
            $token = User_generate_password_recovery_token($user_source);
            engelsystem_email_to_user(
                $user_source,
                _('Password recovery'),
                sprintf(
                    _('Please visit %s to recover your password.'),
                    page_link_to_absolute('user_password_recovery') . '&token=' . $token
                )
            );
            success(_('We sent an email containing your password recovery link.'));
            redirect(page_link_to('login'));
        }
    }

    return User_password_recovery_view();
}

/**
 * User password recovery in 2 steps.
 * (By email)
 *
 * @return string
 */
function user_password_recovery_controller()
{
    if (request()->has('token')) {
        return user_password_recovery_set_new_controller();
    }

    return user_password_recovery_start_controller();
}

/**
 * Menu title for password recovery.
 *
 * @return string
 */
function user_password_recovery_title()
{
    return _('Password recovery');
}

/**
 * Loads a user from param user_id.
 *
 * return array
 */
function load_user()
{
    $request = request();
    if (!$request->has('user_id')) {
        redirect(page_link_to());
    }

    $user = User($request->input('user_id'));

    if ($user == null) {
        error(_('User doesn\'t exist.'));
        redirect(page_link_to());
    }

    return $user;
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return ShiftCalendarRenderer
 */
function shiftCalendarRendererByShiftFilter(ShiftsFilter $shiftsFilter)
{
    $shifts = Shifts_by_ShiftsFilter($shiftsFilter);
    $needed_angeltypes_source = NeededAngeltypes_by_ShiftsFilter($shiftsFilter);
    $shift_entries_source = ShiftEntries_by_ShiftsFilter($shiftsFilter);

    $needed_angeltypes = [];
    $shift_entries = [];
    foreach ($shifts as $shift) {
        $needed_angeltypes[$shift['SID']] = [];
        $shift_entries[$shift['SID']] = [];
    }
    foreach ($shift_entries_source as $shift_entry) {
        if (isset($shift_entries[$shift_entry['SID']])) {
            $shift_entries[$shift_entry['SID']][] = $shift_entry;
        }
    }
    foreach ($needed_angeltypes_source as $needed_angeltype) {
        if (isset($needed_angeltypes[$needed_angeltype['SID']])) {
            $needed_angeltypes[$needed_angeltype['SID']][] = $needed_angeltype;
        }
    }
    unset($needed_angeltypes_source);
    unset($shift_entries_source);

    if (
        in_array(ShiftsFilter::FILLED_FREE, $shiftsFilter->getFilled())
        && in_array(ShiftsFilter::FILLED_FILLED, $shiftsFilter->getFilled())
    ) {
        return new ShiftCalendarRenderer($shifts, $needed_angeltypes, $shift_entries, $shiftsFilter);
    }

    $filtered_shifts = [];
    foreach ($shifts as $shift) {
        $needed_angels_count = 0;
        $taken = 0;
        foreach ($needed_angeltypes[$shift['SID']] as $needed_angeltype) {
            $taken = 0;
            foreach ($shift_entries[$shift['SID']] as $shift_entry) {
                if ($needed_angeltype['angel_type_id'] == $shift_entry['TID'] && $shift_entry['freeloaded'] == 0) {
                    $taken++;
                }
            }

            $needed_angels_count += max(0, $needed_angeltype['count'] - $taken);
        }
        if (in_array(ShiftsFilter::FILLED_FREE, $shiftsFilter->getFilled()) && $taken < $needed_angels_count) {
            $filtered_shifts[] = $shift;
        }
        if (in_array(ShiftsFilter::FILLED_FILLED, $shiftsFilter->getFilled()) && $taken >= $needed_angels_count) {
            $filtered_shifts[] = $shift;
        }
    }

    return new ShiftCalendarRenderer($filtered_shifts, $needed_angeltypes, $shift_entries, $shiftsFilter);
}
