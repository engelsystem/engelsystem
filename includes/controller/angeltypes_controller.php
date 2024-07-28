<?php

use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\UserAngelType;
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftsFilterRenderer;
use Engelsystem\ValidationResult;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * Text for Angeltype related links.
 *
 * @return string
 */
function angeltypes_title()
{
    return __('angeltypes.angeltypes');
}

/**
 * Route angeltype actions.
 *
 * @return array
 */
function angeltypes_controller()
{
    $action = strip_request_item('action', 'list');

    return match ($action) {
        'view'   => angeltype_controller(),
        'edit'   => angeltype_edit_controller(),
        'delete' => angeltype_delete_controller(),
        'list'   => angeltypes_list_controller(),
        default  => angeltypes_list_controller(),
    };
}

/**
 * Path to angeltype view.
 *
 * @param int   $angeltype_id AngelType id
 * @param array $params       additional params
 * @return string
 */
function angeltype_link($angeltype_id, $params = [])
{
    $params = array_merge(['action' => 'view', 'angeltype_id' => $angeltype_id], $params);
    return url('/angeltypes', $params);
}

/**
 * Delete an Angeltype.
 *
 * @return array
 */
function angeltype_delete_controller()
{
    if (!auth()->can('admin_angel_types')) {
        throw_redirect(url('/angeltypes'));
    }

    $angeltype = AngelType::findOrFail(request()->input('angeltype_id'));

    if (request()->hasPostData('delete')) {
        $angeltype->delete();
        engelsystem_log('Deleted angeltype: ' . AngelType_name_render($angeltype, true));
        success(sprintf(__('Angeltype %s deleted.'), $angeltype->name));
        throw_redirect(url('/angeltypes'));
    }

    return [
        sprintf(__('Delete angeltype %s'), htmlspecialchars($angeltype->name)),
        AngelType_delete_view($angeltype),
    ];
}

/**
 * Change an Angeltype.
 *
 * @return array
 */
function angeltype_edit_controller()
{
    // In supporter mode only allow to modify description
    $supporter_mode = !auth()->can('admin_angel_types');
    $request = request();

    if ($request->has('angeltype_id')) {
        // Edit existing angeltype
        $angeltype = AngelType::findOrFail($request->input('angeltype_id'));

        if (!auth()->user()?->isAngelTypeSupporter($angeltype) && !auth()->can('admin_user_angeltypes')) {
            throw_redirect(url('/angeltypes'));
        }
    } else {
        // New angeltype
        if ($supporter_mode) {
            // Supporters aren't allowed to create new angeltypes.
            throw_redirect(url('/angeltypes'));
        }
        $angeltype = new AngelType();
    }

    if ($request->hasPostData('submit')) {
        $valid = true;

        if (!$supporter_mode) {
            if ($request->has('name')) {
                $result = AngelType_validate_name($request->postData('name'), $angeltype);
                $angeltype->name = substr($result->getValue(), 0, 255);
                if (!$result->isValid()) {
                    $valid = false;
                    error(__('Please check the name. Maybe it already exists.'));
                }
            }

            $angeltype->restricted = $request->has('restricted');
            $angeltype->shift_self_signup = $request->has('shift_self_signup');
            $angeltype->show_on_dashboard = $request->has('show_on_dashboard');
            $angeltype->hide_register = $request->has('hide_register');
            $angeltype->hide_on_shift_view = $request->has('hide_on_shift_view');

            $angeltype->requires_driver_license = $request->has('requires_driver_license');
            $angeltype->requires_ifsg_certificate = $request->has('requires_ifsg_certificate');
        }

        $angeltype->description = strip_request_item_nl('description', $angeltype->description);

        $angeltype->contact_name = strip_request_item('contact_name', $angeltype->contact_name);
        $angeltype->contact_dect = strip_request_item('contact_dect', $angeltype->contact_dect) ?: '';
        $angeltype->contact_email = strip_request_item('contact_email', $angeltype->contact_email);

        if ($valid) {
            $angeltype->save();

            success(__('Angel type saved.'));
            engelsystem_log(
                'Saved angeltype: ' . $angeltype->name . ($angeltype->restricted ? ', restricted' : '')
                . ($angeltype->shift_self_signup ? ', shift_self_signup' : '')
                . (config('driving_license_enabled')
                    ? (($angeltype->requires_driver_license ? ', requires driver license' : '') . ', ')
                    : '')
                . (config('ifsg_enabled')
                    ? (($angeltype->requires_ifsg_certificate ? ', requires ifsg certificate' : '') . ', ')
                    : '')
                . $angeltype->contact_name . ', '
                . $angeltype->contact_dect . ', '
                . $angeltype->contact_email . ', '
                . $angeltype->show_on_dashboard . ', '
                . $angeltype->hide_register . ', '
                . $angeltype->hide_on_shift_view
            );
            throw_redirect(angeltype_link($angeltype->id));
        }
    }

    return [
        sprintf(__('Edit %s'), htmlspecialchars((string) $angeltype->name)),
        AngelType_edit_view($angeltype, $supporter_mode),
    ];
}

/**
 * View details of a given angeltype.
 *
 * @return array
 */
function angeltype_controller()
{
    $user = auth()->user();

    if (!auth()->can('angeltypes')) {
        throw_redirect(url('/'));
    }

    $angeltype = AngelType::findOrFail(request()->input('angeltype_id'));
    /** @var UserAngelType $user_angeltype */
    $user_angeltype = UserAngelType::whereUserId($user->id)->where('angel_type_id', $angeltype->id)->first();
    $members = $angeltype->userAngelTypes
        ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
        ->load(['state', 'personalData', 'contact']);
    $days = angeltype_controller_shiftsFilterDays($angeltype);
    $shiftsFilter = angeltype_controller_shiftsFilter($angeltype, $days);
    if (request()->input('showFilledShifts')) {
        $shiftsFilter->setFilled([ShiftsFilter::FILLED_FREE, ShiftsFilter::FILLED_FILLED]);
    }

    $shiftsFilterRenderer = new ShiftsFilterRenderer($shiftsFilter);
    $shiftsFilterRenderer->enableDaySelection($days);

    $shiftCalendarRenderer = shiftCalendarRendererByShiftFilter($shiftsFilter);
    $request = request();
    $tab = 0;

    if ($request->has('shifts_filter_day') || $request->has('showShiftsTab')) {
        $tab = 1;
    }

    $isSupporter = !is_null($user_angeltype) && $user_angeltype->supporter;
    return [
        sprintf(__('Team %s'), htmlspecialchars($angeltype->name)),
        AngelType_view(
            $angeltype,
            $members,
            $user_angeltype,
            auth()->can('admin_user_angeltypes') || $isSupporter,
            auth()->can('admin_angel_types'),
            $isSupporter,
            $user->license,
            $user,
            $shiftsFilterRenderer,
            $shiftCalendarRenderer,
            $tab
        ),
    ];
}

/**
 * On which days do shifts for this angeltype occur? Needed for shiftCalendar.
 *
 * @param AngelType $angeltype
 * @return array
 */
function angeltype_controller_shiftsFilterDays(AngelType $angeltype)
{
    $all_shifts = Shifts_by_angeltype($angeltype);
    $days = [];
    foreach ($all_shifts as $shift) {
        $day = Carbon::make($shift['start'])->format('Y-m-d');
        if (!isset($days[$day])) {
            $days[$day] = dateWithEventDay($day);
        }
    }
    ksort($days);
    return $days;
}

/**
 * Sets up the shift filter for the angeltype.
 *
 * @param AngelType $angeltype
 * @param array     $days
 * @return ShiftsFilter
 */
function angeltype_controller_shiftsFilter(AngelType $angeltype, $days)
{
    $request = request();
    $locationIds = Location::query()
        ->select('id')
        ->pluck('id')
        ->toArray();
    $shiftsFilter = new ShiftsFilter(
        auth()->can('user_shifts_admin'),
        $locationIds,
        [$angeltype->id]
    );
    $selected_day = date('Y-m-d');
    if (!empty($days) && !isset($days[$selected_day])) {
        $selected_day = array_key_first($days);
    }
    if ($request->input('shifts_filter_day')) {
        $selected_day = $request->input('shifts_filter_day');
    }
    $shiftsFilter->setStartTime(parse_date('Y-m-d H:i', $selected_day . ' 00:00'));
    $shiftsFilter->setEndTime(parse_date('Y-m-d H:i', $selected_day . ' 23:59'));

    return $shiftsFilter;
}

/**
 * View a list of all angeltypes.
 *
 * @return array
 */
function angeltypes_list_controller()
{
    $user = auth()->user();
    $admin_angeltypes = auth()->can('admin_angel_types');

    if (!auth()->can('angeltypes')) {
        throw_redirect(url('/'));
    }

    $angeltypes = AngelTypes_with_user($user->id);
    foreach ($angeltypes as $angeltype) {
        $actions = [
            button(
                url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]),
                icon('eye') . ($admin_angeltypes ? '' : __('View')),
                'btn-sm btn-info',
                '',
                ($admin_angeltypes ? __('View') : '')
            ),
        ];

        if ($admin_angeltypes) {
            $actions[] = button(
                url('/angeltypes', ['action' => 'edit', 'angeltype_id' => $angeltype->id]),
                icon('pencil'),
                'btn-sm',
                '',
                __('form.edit')
            );
            $actions[] = button(
                url('/angeltypes', ['action' => 'delete', 'angeltype_id' => $angeltype->id]),
                icon('trash'),
                'btn-sm btn-danger',
                '',
                __('form.delete')
            );
        }

        $angeltype->membership = AngelType_render_membership($angeltype);
        if (!empty($angeltype->user_angel_type_id)) {
            $actions[] = button(
                url(
                    '/user-angeltypes',
                    ['action' => 'delete', 'user_angeltype_id' => $angeltype->user_angel_type_id]
                ),
                icon('box-arrow-right') . ($admin_angeltypes ? '' : __('Leave')),
                'btn-sm',
                '',
                ($admin_angeltypes ? __('Leave') : '')
            );
        } else {
            $actions[] = button(
                url('/user_angeltypes', ['action' => 'add', 'angeltype_id' => $angeltype->id]),
                icon('box-arrow-in-right') . ($admin_angeltypes ? '' : __('Join')),
                'btn-sm' . ($admin_angeltypes ? ' btn-success' : ''),
                '',
                ($admin_angeltypes ? __('Join') : '')
            );
        }

        $angeltype->is_restricted = $angeltype->restricted ? icon('mortarboard-fill') : '';
        $angeltype->shift_self_signup_allowed = $angeltype->shift_self_signup ? icon('pencil-square') : '';

        $angeltype->name = '<a href="'
            . url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id])
            . '">'
            . htmlspecialchars($angeltype->name)
            . '</a>';

        $angeltype->actions = table_buttons($actions);
    }

    return [
        angeltypes_title(),
        AngelTypes_list_view($angeltypes, auth()->can('admin_angel_types')),
    ];
}

/**
 * Validates a name for angeltypes.
 * Returns ValidationResult containing validation success and validated name.
 *
 * @param string    $name Wanted name for the angeltype
 * @param AngelType $angeltype The angeltype the name is for
 *
 * @return ValidationResult result and validated name
 */
function AngelType_validate_name($name, AngelType $angeltype)
{
    $name = strip_item($name);
    if ($name == '') {
        return new ValidationResult(false, '');
    }
    if ($angeltype->id) {
        $valid = AngelType::whereName($name)
                ->where('id', '!=', $angeltype->id)
                ->count() == 0;
        return new ValidationResult($valid, $name);
    }

    $valid = AngelType::whereName($name)->count() == 0;
    return new ValidationResult($valid, $name);
}

/**
 * Returns all angeltypes and subscription state to each of them for given user.
 *
 * @param int $userId
 * @return Collection|AngelType[]
 */
function AngelTypes_with_user($userId): Collection
{
    return AngelType::query()
        ->select([
            'angel_types.*',
            'user_angel_type.id AS user_angel_type_id',
            'user_angel_type.confirm_user_id',
            'user_angel_type.supporter',
        ])
        ->leftJoin('user_angel_type', function (JoinClause $join) use ($userId) {
            $join->on('angel_types.id', 'user_angel_type.angel_type_id');
            $join->where('user_angel_type.user_id', $userId);
        })
        ->get();
}
