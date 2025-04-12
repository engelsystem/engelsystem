<?php

use Engelsystem\Database\Db;
use Engelsystem\Helpers\Goodie;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Engelsystem\ShiftCalendarRenderer;
use Engelsystem\ShiftsFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        throw_redirect(url('/'));
    }

    $action = 'list';
    if ($request->has('action')) {
        $action = $request->input('action');
    }

    return match ($action) {
        'view'          => user_controller(),
        'delete'        => user_delete_controller(),
        'list'          => users_list_controller(),
        default         => users_list_controller(),
    };
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
        $user_source = User::findOrFail($request->query->get('user_id'));
    } else {
        $user_source = $user;
    }

    if (!auth()->can('admin_user')) {
        throw_redirect(url('/'));
    }

    // You cannot delete yourself
    if ($user->id == $user_source->id) {
        error(__('You cannot delete yourself.'));
        throw_redirect(user_link($user->id));
    }

    if ($request->hasPostData('submit')) {
        $valid = true;

        if (
            !(
            $request->has('password')
            && $auth->verifyPassword($user, $request->postData('password'))
            )
        ) {
            $valid = false;
            error(__('auth.password.error'));
        }

        if ($valid) {
            // Move user created news/answers/worklogs/shifts  etc. to deleting user
            $user_source->news()->update(['user_id' => $user->id]);
            $user_source->questionsAnswered()->update(['answerer_id' => $user->id]);
            $user_source->worklogsCreated()->update(['creator_id' => $user->id]);
            $user_source->shiftsCreated()->update(['created_by' => $user->id]);
            $user_source->shiftsUpdated()->update(['updated_by' => $user->id]);

            // Load data before user deletion to prevent errors when displaying
            $user_source->load(['contact', 'personalData', 'settings', 'state']);
            $user_source->delete();

            mail_user_delete($user_source);
            success(__('User deleted.'));
            engelsystem_log(sprintf('Deleted user %s', User_Nick_render($user_source, true)));

            throw_redirect(users_link());
        }
    }

    return [
        sprintf(__('Delete %s'), htmlspecialchars($user_source->displayName)),
        User_delete_view($user_source),
    ];
}

/**
 * @return string
 */
function users_link()
{
    return url('/users');
}

/**
 * @param int $userId
 * @return string
 */
function user_edit_link($userId)
{
    return url('/admin-user', ['user_id' => $userId]);
}

/**
 * @param int $userId
 * @return string
 */
function user_delete_link($userId)
{
    return url('/users', ['action' => 'delete', 'user_id' => $userId]);
}

/**
 * @param int $userId
 * @return string
 */
function user_link($userId)
{
    return url('/users', ['action' => 'view', 'user_id' => $userId]);
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
            throw_redirect(url('/'));
        }
    }

    $shifts = Shifts_by_user($user_source->id, true);
    foreach ($shifts as $shift) {
        // TODO: Move queries to model
        $shift->needed_angeltypes = Db::select(
            '
            SELECT DISTINCT `angel_types`.*
            FROM `shift_entries`
            JOIN `angel_types` ON `shift_entries`.`angel_type_id`=`angel_types`.`id`
            WHERE `shift_entries`.`shift_id` = ?
            ORDER BY `angel_types`.`name`
            ',
            [$shift->id]
        );
        $neededAngeltypes = $shift->needed_angeltypes;
        foreach ($neededAngeltypes as &$needed_angeltype) {
            $needed_angeltype['users'] = User::query()
                ->select(['users.*', 'shift_entries.freeloaded_by'])
                ->from('shift_entries')
                ->join('users', 'shift_entries.user_id', 'users.id')
                ->where('shift_entries.shift_id', $shift->id)
                ->where('shift_entries.angel_type_id', $needed_angeltype['id'])
                ->with('state')
                ->get();
        }
        $shift->needed_angeltypes = $neededAngeltypes;
    }

    if (empty($user_source->api_key)) {
        auth()->resetApiKey($user_source);
    }

    $goodie_score = sprintf('%.2f', Goodie::userScore($user_source)) . '&nbsp;h';
    if ($user_source->state->force_active && config('enable_force_active')) {
        $goodie_score = '<span title="' . $goodie_score . '">' . __('user.goodie_score.enough') . '</span>';
    }

    $worklogs = $user_source->worklogs()
        ->with(['user', 'creator'])
        ->get();

    $is_ifsg_supporter = (bool) AngelType::whereRequiresIfsgCertificate(true)
        ->leftJoin('user_angel_type', 'user_angel_type.angel_type_id', 'angel_types.id')
        ->where('user_angel_type.user_id', $user->id)
        ->where('user_angel_type.supporter', true)
        ->count();

    $is_drive_supporter = (bool) AngelType::whereRequiresDriverLicense(true)
        ->leftJoin('user_angel_type', 'user_angel_type.angel_type_id', 'angel_types.id')
        ->where('user_angel_type.user_id', $user->id)
        ->where('user_angel_type.supporter', true)
        ->count();

    return [
        htmlspecialchars($user_source->displayName),
        User_view(
            $user_source,
            auth()->can('admin_user'),
            $user_source->isFreeloader(),
            $user_source->userAngelTypes,
            $user_source->groups,
            $shifts,
            $user->id == $user_source->id,
            $goodie_score,
            auth()->can('user.goodie.edit'),
            auth()->can('admin_user_worklog'),
            $worklogs,
            auth()->can('user.ifsg.edit')
                || $is_ifsg_supporter
                || auth()->can('user.drive.edit')
                || $is_drive_supporter,
        ),
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
        throw_redirect(url('/'));
    }

    $order_by = 'name';
    if (
        $request->has('OrderBy') && in_array($request->input('OrderBy'), [
            'name',
            'first_name',
            'last_name',
            'dect',
            'arrived',
            'got_voucher',
            'freeloads',
            'active',
            'force_active',
            'got_goodie',
            'shirt_size',
            'planned_arrival_date',
            'planned_departure_date',
            'last_login_at',
        ])
    ) {
        $order_by = $request->input('OrderBy');
    }

    /** @var User[]|Collection $users */
    $users = User::with(['contact', 'personalData', 'state', 'shiftEntries' => function (HasMany $query) {
        $query->whereNotNull('freeloaded_by');
    }])
        ->orderBy('name')
        ->get();
    foreach ($users as $user) {
        $user->setAttribute(
            'freeloads',
            $user->shiftEntries
                ->whereNotNull('freeloaded_by')
                ->count()
        );
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
            ShiftEntry::whereNotNull('freeloaded_by')->count(),
            State::whereGotGoodie(true)->count(),
            State::query()->sum('got_voucher'),
            auth()->can('admin_user'),
        ),
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
        throw_redirect(url('/'));
    }

    $user = User::find($request->input('user_id'));
    if (!$user) {
        error(__('User doesn\'t exist.'));
        throw_redirect(url('/'));
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
    /** @var ShiftEntry[][] $shift_entries */
    $shift_entries = [];
    foreach ($shifts as $shift) {
        $needed_angeltypes[$shift->id] = [];
        $shift_entries[$shift->id] = [];
    }

    foreach ($shift_entries_source as $shift_entry) {
        if (isset($shift_entries[$shift_entry->shift_id])) {
            $shift_entries[$shift_entry->shift_id][] = $shift_entry;
        }
    }

    foreach ($needed_angeltypes_source as $needed_angeltype) {
        if (isset($needed_angeltypes[$needed_angeltype['shift_id']])) {
            $needed_angeltypes[$needed_angeltype['shift_id']][] = $needed_angeltype;
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

    $own_shift_ids = [];
    if (in_array(ShiftsFilter::FILLED_OWN, $shiftsFilter->getFilled())) {
        $own_shift_ids = Shifts_by_user(auth()->user()->id)->pluck('id')->toArray();
    }

    $filtered_shifts = [];
    foreach ($shifts as $shift) {
        $needed_angels_count = 0;
        foreach ($needed_angeltypes[$shift->id] as $needed_angeltype) {
            $taken = 0;

            if (
                !in_array(ShiftsFilter::FILLED_FILLED, $shiftsFilter->getFilled())
                && !in_array($needed_angeltype['angel_type_id'], $shiftsFilter->getTypes())
            ) {
                continue;
            }

            foreach ($shift_entries[$shift->id] as $shift_entry) {
                if (
                    $needed_angeltype['angel_type_id'] == $shift_entry->angel_type_id
                    && !$shift_entry->freeloaded_by
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
            continue;
        }

        if (
            in_array(ShiftsFilter::FILLED_FILLED, $shiftsFilter->getFilled())
            && $needed_angels_count == 0
        ) {
            $filtered_shifts[] = $shift;
            continue;
        }

        if (
            in_array(ShiftsFilter::FILLED_OWN, $shiftsFilter->getFilled())
            && in_array($shift->id, $own_shift_ids)
        ) {
            $filtered_shifts[] = $shift;
        }
    }

    return new ShiftCalendarRenderer($filtered_shifts, $needed_angeltypes, $shift_entries, $shiftsFilter);
}

/**
 * Generates a hint, if user joined angeltypes that require a driving license and the user has no driver license
 * information provided.
 *
 * @return string|null
 */
function user_driver_license_required_hint()
{
    $user = auth()->user();

    // User has already entered data, no hint needed.
    if (!config('driving_license_enabled') || $user->license->wantsToDrive()) {
        return null;
    }

    $angeltypes = $user->userAngelTypes;
    foreach ($angeltypes as $angeltype) {
        if ($angeltype->requires_driver_license) {
            return sprintf(
                __('angeltype.driving_license.required.info.here'),
                '<a href="' . url('/settings/certificates') . '">' . __('driving_license.info') . '</a>'
            );
        }
    }

    return null;
}

function user_ifsg_certificate_required_hint()
{
    $user = auth()->user();

    // User has already entered data, no hint needed.
    if (!config('ifsg_enabled') || $user->license->ifsg_light || $user->license->ifsg) {
        return null;
    }

    $angeltypes = $user->userAngelTypes;
    foreach ($angeltypes as $angeltype) {
        if (
            $angeltype->requires_ifsg_certificate && !(
                $user->license->ifsg_certificate || $user->license->ifsg_certificate_light
            )
        ) {
            return sprintf(
                __('angeltype.ifsg.required.info.here'),
                '<a href="' . url('/settings/certificates') . '">' . __('ifsg.info') . '</a>'
            );
        }
    }

    return null;
}
