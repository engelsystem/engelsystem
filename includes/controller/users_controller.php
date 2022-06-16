<?php

use Engelsystem\Database\Db;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\ShiftCalendarRenderer;
use Engelsystem\ShiftsFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Route user actions.
 *
 * @return array
 */
function users_controller()
{
    $user = auth()->user();
    $request = request();

    if (!$user) {
        throw_redirect(page_link_to());
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
    $user = auth()->user();
    $auth = auth();
    $request = request();

    if ($request->has('user_id')) {
        $user_source = User::find($request->query->get('user_id'));
    } else {
        $user_source = $user;
    }

    if (!auth()->can('admin_user')) {
        throw_redirect(page_link_to());
    }

    // You cannot delete yourself
    if ($user->id == $user_source->id) {
        error(__('You cannot delete yourself.'));
        throw_redirect(user_link($user->id));
    }

    if ($request->hasPostData('submit')) {
        $valid = true;

        if (!(
            $request->has('password')
            && $auth->verifyPassword($user, $request->postData('password'))
        )) {
            $valid = false;
            error(__('auth.password.error'));
        }

        if ($valid) {
            // Load data before user deletion to prevent errors when displaying
            $user_source->load(['contact', 'personalData', 'settings', 'state']);
            $user_source->delete();

            mail_user_delete($user_source);
            success(__('User deleted.'));
            engelsystem_log(sprintf('Deleted %s', User_Nick_render($user_source, true)));

            throw_redirect(users_link());
        }
    }

    return [
        sprintf(__('Delete %s'), $user_source->name),
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
 * @param int $userId
 * @return string
 */
function user_edit_link($userId)
{
    return page_link_to('admin_user', ['user_id' => $userId]);
}

/**
 * @param int $userId
 * @return string
 */
function user_delete_link($userId)
{
    return page_link_to('users', ['action' => 'delete', 'user_id' => $userId]);
}

/**
 * @param int $userId
 * @return string
 */
function user_link($userId)
{
    return page_link_to('users', ['action' => 'view', 'user_id' => $userId]);
}

/**
 * @return array
 */
function user_edit_vouchers_controller()
{
    $user = auth()->user();
    $request = request();

    if ($request->has('user_id')) {
        $user_source = User::find($request->input('user_id'));
    } else {
        $user_source = $user;
    }

    if (
        (!auth()->can('admin_user') && !auth()->can('voucher.edit'))
        || !config('enable_voucher')
    ) {
        throw_redirect(page_link_to());
    }

    if ($request->hasPostData('submit')) {
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
            error(__('Please enter a valid number of vouchers.'));
        }

        if ($valid) {
            $user_source->state->got_voucher = $vouchers;
            $user_source->state->save();

            success(__('Saved the number of vouchers.'));
            engelsystem_log(User_Nick_render($user_source, true) . ': ' . sprintf('Got %s vouchers',
                    $user_source->state->got_voucher));

            throw_redirect(user_link($user_source->id));
        }
    }

    return [
        sprintf(__('%s\'s vouchers'), $user_source->name),
        User_edit_vouchers_view($user_source)
    ];
}

/**
 * @return array
 */
function user_controller()
{
    $user = auth()->user();
    $request = request();

    $user_source = $user;
    if ($request->has('user_id')) {
        $user_source = User::find($request->input('user_id'));
        if (!$user_source) {
            error(__('User not found.'));
            throw_redirect(page_link_to('/'));
        }
    }

    $shifts = Shifts_by_user($user_source->id, auth()->can('user_shifts_admin'));
    foreach ($shifts as &$shift) {
        // TODO: Move queries to model
        $shift['needed_angeltypes'] = Db::select('
            SELECT DISTINCT `AngelTypes`.*
            FROM `ShiftEntry`
            JOIN `AngelTypes` ON `ShiftEntry`.`TID`=`AngelTypes`.`id`
            WHERE `ShiftEntry`.`SID` = ?
            ORDER BY `AngelTypes`.`name`
            ',
            [$shift['SID']]
        );
        foreach ($shift['needed_angeltypes'] as &$needed_angeltype) {
            $needed_angeltype['users'] = Db::select('
                    SELECT `ShiftEntry`.`freeloaded`, `users`.*
                    FROM `ShiftEntry`
                    JOIN `users` ON `ShiftEntry`.`UID`=`users`.`id`
                    WHERE `ShiftEntry`.`SID` = ?
                    AND `ShiftEntry`.`TID` = ?
                ',
                [$shift['SID'], $needed_angeltype['id']]
            );
        }
    }

    if (empty($user_source->api_key)) {
        User_reset_api_key($user_source, false);
    }

    if ($user_source->state->force_active) {
        $tshirt_score = __('Enough');
    } else {
        $tshirt_score = sprintf('%.2f', User_tshirt_score($user_source->id)) . '&nbsp;h';
    }

    return [
        $user_source->name,
        User_view(
            $user_source,
            auth()->can('admin_user'),
            User_is_freeloader($user_source),
            User_angeltypes($user_source->id),
            User_groups($user_source->id),
            $shifts,
            $user->id == $user_source->id,
            $tshirt_score,
            auth()->can('admin_active'),
            auth()->can('admin_user_worklog'),
            UserWorkLogsForUser($user_source->id)
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
    $request = request();

    if (!auth()->can('admin_user')) {
        throw_redirect(page_link_to());
    }

    $order_by = 'name';
    if ($request->has('OrderBy') && in_array($request->input('OrderBy'), [
            'name',
            'first_name',
            'last_name',
            'dect',
            'arrived',
            'got_voucher',
            'freeloads',
            'active',
            'force_active',
            'got_shirt',
            'shirt_size',
            'planned_arrival_date',
            'planned_departure_date',
            'last_login_at',
        ])) {
        $order_by = $request->input('OrderBy');
    }

    /** @var User[]|Collection $users */
    $users = User::with(['contact', 'personalData', 'state'])
        ->orderBy('name')
        ->get();
    foreach ($users as $user) {
        $user->setAttribute('freeloads', count(ShiftEntries_freeloaded_by_user($user->id)));
    }

    $users = $users->sortBy(function (User $user) use ($order_by) {
        $userData = $user->toArray();
        $data = [];
        array_walk_recursive($userData, function ($value, $key) use (&$data) {
            $data[$key] = $value;
        });

        return isset($data[$order_by]) ? Str::lower($data[$order_by]) : null;
    });

    return [
        __('All users'),
        Users_view(
            $users,
            $order_by,
            State::whereArrived(true)->count(),
            State::whereActive(true)->count(),
            State::whereForceActive(true)->count(),
            ShiftEntries_freeloaded_count(),
            State::whereGotShirt(true)->count(),
            State::query()->sum('got_voucher')
        )
    ];
}

/**
 * Loads a user from param user_id.
 *
 * @return User
 */
function load_user()
{
    $request = request();
    if (!$request->has('user_id')) {
        throw_redirect(page_link_to());
    }

    $user = User::find($request->input('user_id'));
    if (!$user) {
        error(__('User doesn\'t exist.'));
        throw_redirect(page_link_to());
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
        foreach ($needed_angeltypes[$shift['SID']] as $needed_angeltype) {
            $taken = 0;

            if (
                !in_array(ShiftsFilter::FILLED_FILLED, $shiftsFilter->getFilled())
                && !in_array($needed_angeltype['angel_type_id'], $shiftsFilter->getTypes())
            ) {
                continue;
            }

            foreach ($shift_entries[$shift['SID']] as $shift_entry) {
                if (
                    $needed_angeltype['angel_type_id'] == $shift_entry['TID']
                    && $shift_entry['freeloaded'] == 0
                ) {
                    $taken++;
                }
            }

            $needed_angels_count += max(0, $needed_angeltype['count'] - $taken);
        }

        if (
            in_array(ShiftsFilter::FILLED_FREE, $shiftsFilter->getFilled())
            && $needed_angels_count > 0
        ) {
            $filtered_shifts[] = $shift;
        }

        if (
            in_array(ShiftsFilter::FILLED_FILLED, $shiftsFilter->getFilled())
            && $needed_angels_count == 0
        ) {
            $filtered_shifts[] = $shift;
        }
    }

    return new ShiftCalendarRenderer($filtered_shifts, $needed_angeltypes, $shift_entries, $shiftsFilter);
}
