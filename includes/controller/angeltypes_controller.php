<?php

use Engelsystem\Controllers\AngelTypesController;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\UserAngelType;
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftsFilterRenderer;

/**
 * Route angeltype actions.
 *
 * @return array
 */
function angeltypes_controller()
{
    $action = request()->input('action');
    ;

    return match ($action) {
        'view'   => angeltype_controller(),
        'list'   => throw_redirect(url('/angeltypes')),
        default  =>  ['', app(AngelTypesController::class)->index()->getContent()],
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
 * View details of a given angeltype.
 *
 * @return array
 */
function angeltype_controller()
{
    $user = auth()->user();

    if (!auth()->can('angeltypes.view')) {
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
            auth()->can('userangeltypes.edit') || $isSupporter,
            auth()->can('angeltypes.edit'),
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
