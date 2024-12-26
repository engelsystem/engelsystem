<?php

use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftSignupStatus;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\ShiftSignupState;
use Illuminate\Support\Str;

/**
 * @param array|Shift $shift
 * @return string
 */
function shift_link($shift)
{
    $parameters = ['action' => 'view'];
    if (isset($shift['shift_id']) || isset($shift['id'])) {
        $parameters['shift_id'] = $shift['shift_id'] ?? $shift['id'];
    }

    return url('/shifts', $parameters);
}

/**
 * @param Shift $shift
 * @return string
 */
function shift_edit_link(Shift $shift)
{
    return url('/user-shifts', ['edit_shift' => $shift->id]);
}

/**
 * Edit a single shift.
 *
 * @return string
 */
function shift_edit_controller()
{
    $valid = true;
    $request = request();

    if (!auth()->can('admin_shifts')) {
        throw_redirect(url('/user-shifts'));
    }

    if (!$request->has('edit_shift') || !test_request_int('edit_shift')) {
        throw_redirect(url('/user-shifts'));
    }
    $shift_id = $request->input('edit_shift');

    $shift = Shift::findOrFail($shift_id);
    if (ScheduleShift::whereShiftId($shift->id)->first()) {
        warning(__(
            'This shift was imported from a schedule so some changes will be overwritten with the next import.'
        ));
    }

    $locations = [];
    foreach (Location::orderBy('name')->get() as $location) {
        $locations[$location->id] = $location->name;
    }
    $angeltypes = AngelType::all()->pluck('name', 'id')->toArray();
    $shifttypes = ShiftType::all()->pluck('name', 'id')->toArray();

    $needed_angel_types = collect(NeededAngelTypes_by_shift($shift))->pluck('count', 'angel_type_id')->toArray();
    foreach (array_keys($angeltypes) as $angeltype_id) {
        if (!isset($needed_angel_types[$angeltype_id])) {
            $needed_angel_types[$angeltype_id] = 0;
        }
    }

    $shifttype_id = $shift->shift_type_id;
    $title = $shift->title;
    $description = $shift->description;
    $rid = $shift->location_id;
    $start = $shift->start;
    $end = $shift->end;

    if ($request->hasPostData('submit')) {
        // Name/Bezeichnung der Schicht, darf leer sein
        $title = strip_request_item('title');
        $description = strip_request_item_nl('description');

        // Auswahl der sichtbaren Locations für die Schichten
        if (
            $request->has('rid')
            && preg_match('/^\d+$/', $request->input('rid'))
            && isset($locations[$request->input('rid')])
        ) {
            $rid = $request->input('rid');
        } else {
            $valid = false;
            error(__('Please select a location.'));
        }

        if ($request->has('shifttype_id') && isset($shifttypes[$request->input('shifttype_id')])) {
            $shifttype_id = $request->input('shifttype_id');
        } else {
            $valid = false;
            error(__('Please select a shift type.'));
        }

        if ($request->has('start') && $tmp = DateTime::createFromFormat('Y-m-d\TH:i', $request->input('start'))) {
            $start = $tmp;
        } else {
            $valid = false;
            error(__('Please enter a valid starting time for the shifts.'));
        }

        if ($request->has('end') && $tmp = DateTime::createFromFormat('Y-m-d\TH:i', $request->input('end'))) {
            $end = $tmp;
        } else {
            $valid = false;
            error(__('Please enter a valid ending time for the shifts.'));
        }

        if ($start >= $end) {
            $valid = false;
            error(__('The ending time has to be after the starting time.'));
        }

        foreach ($needed_angel_types as $needed_angeltype_id => $count) {
            $needed_angel_types[$needed_angeltype_id] = 0;

            $queryKey = 'angeltype_count_' . $needed_angeltype_id;
            if ($request->has($queryKey)) {
                if (test_request_int($queryKey)) {
                    $needed_angel_types[$needed_angeltype_id] = trim($request->input($queryKey));
                } else {
                    $valid = false;
                    error(sprintf(
                        __('Please check your input for needed angels of type %s.'),
                        $angeltypes[$needed_angeltype_id]
                    ));
                }
            }
        }

        if ($valid) {
            $oldShift = Shift::find($shift->id);

            $shift->shift_type_id = $shifttype_id;
            $shift->title = $title;
            $shift->description = $description;
            $shift->location_id = $rid;
            $shift->start = $start;
            $shift->end = $end;
            $shift->updatedBy()->associate(auth()->user());
            $shift->save();

            event('shift.updating', [
                'shift' => $shift,
                'oldShift' => $oldShift,
            ]);

            NeededAngelType::whereShiftId($shift_id)->delete();
            $needed_angel_types_info = [];
            foreach ($needed_angel_types as $type_id => $count) {
                $angeltype = AngelType::find($type_id);
                if (!empty($angeltype) && $count > 0) {
                    $neededAngelType = new NeededAngelType();
                    $neededAngelType->shift()->associate($shift);
                    $neededAngelType->angel_type_id = $type_id;
                    $neededAngelType->count = $count;
                    $neededAngelType->save();

                    $needed_angel_types_info[] = $angeltypes[$type_id] . ': ' . $count;
                }
            }

            engelsystem_log(
                'Updated shift \'' . $shifttypes[$shifttype_id] . ', ' . $title
                . '\' from ' . $start->format('Y-m-d H:i')
                . ' to ' . $end->format('Y-m-d H:i')
                . ' with angel types ' . join(', ', $needed_angel_types_info)
                . ' and description ' . $description
            );
            success(__('Shift updated.'));

            throw_redirect(shift_link($shift));
        }
    }

    $angel_types_spinner = '';
    foreach ($angeltypes as $angeltype_id => $angeltype_name) {
        $angel_types_spinner .=
            '<div class="col-sm-6 col-md-8 col-lg-6 col-xl-4 col-xxl-3">'
            . form_spinner(
                'angeltype_count_' . $angeltype_id,
                htmlspecialchars($angeltype_name),
                $needed_angel_types[$angeltype_id],
                [],
                (bool) ScheduleShift::whereShiftId($shift->id)->first(),
            )
            . '</div>';
    }

    $link = button(url('/shifts', ['action' => 'view', 'shift_id' => $shift_id]), icon('chevron-left'), 'btn-sm', '', __('general.back'));
    return page_with_title(
        $link . ' ' . shifts_title(),
        [
            msg(),
            '<noscript>'
            . info(__('This page is much more comfortable with javascript.'), true)
            . '</noscript>',
            form([
                div('row', [
                    div('col-md-6 col-xl-5', [
                        form_select('shifttype_id', __('Shift type'), $shifttypes, $shifttype_id),
                        form_text('title', __('title.title'), $title),
                        form_select('rid', __('Location'), $locations, $rid),
                    ]),
                    div('col-md-6 col-xl-7', [
                        form_textarea('description', __('Additional description'), $description),
                        form_info(
                            '',
                            __('This description is for single shifts, otherwise please use the description in shift type.')
                        ),
                    ]),
                ]),
                div('row', [
                    div('col-md-6 col-xl-5', [
                        div('row', [
                            div('col-lg-6', [
                                form_datetime('start', __('shifts.start'), $start),
                            ]),
                            div('col-lg-6', [
                                form_datetime('end', __('shifts.end'), $end),
                            ]),
                        ]),
                    ]),
                    div('col-md-6 col-xl-7', [
                        '<h4>' . __('Needed angels') . '</h4>',
                        div('row', [
                            $angel_types_spinner,
                        ]),
                    ]),
                ]),
                form_submit('submit', icon('save') . __('form.save')),
            ]),
        ]
    );
}

function shift_delete_controller(): void
{
    $request = request();

    // Only accessible for admins / ShiCos with user_shifts_admin privileg
    if (!auth()->can('user_shifts_admin')) {
        throw new HttpForbidden();
    }

    // Must contain shift id and confirmation
    if (!$request->has('delete_shift') || !$request->hasPostData('delete')) {
        throw new HttpNotFound();
    }

    $shift_id = $request->input('delete_shift');
    $shift = Shift::findOrFail($shift_id);

    event('shift.deleting', ['shift' => $shift]);

    $shift->delete();

    engelsystem_log(
        'Deleted shift ' . $shift->title . ': ' . $shift->shiftType->name
        . ' from ' . $shift->start->format('Y-m-d H:i')
        . ' to ' . $shift->end->format('Y-m-d H:i')
    );
    success(__('Shift deleted.'));

    /** @var Redirector $redirect */
    $redirect = app('redirect');
    $old = $redirect->back()->getHeaderLine('location');
    if (Str::contains($old, '/shifts') && Str::contains($old, 'action=view')) {
        throw_redirect(url('/user-shifts'));
    }

    throw_redirect($old);
}

/**
 * @return array
 */
function shift_controller()
{
    $user = auth()->user();
    $request = request();

    if (!auth()->can('user_shifts')) {
        throw_redirect(url('/'));
    }

    if (!$request->has('shift_id')) {
        throw_redirect(url('/user-shifts'));
    }

    $shift = Shift::with(['shiftEntries.user.state', 'shiftEntries.angelType'])
        ->findOrFail($request->input('shift_id'));
    $shift = Shift($shift);
    if (empty($shift)) {
        error(__('Shift could not be found.'));
        throw_redirect(url('/user-shifts'));
    }

    $shifttype = $shift->shiftType;
    $location = $shift->location;
    /** @var AngelType[] $angeltypes */
    $angeltypes = AngelType::all();
    $user_shifts = Shifts_by_user($user->id);

    $shift_signup_state = new ShiftSignupState(ShiftSignupStatus::OCCUPIED, 0);
    foreach ($angeltypes as $angeltype) {
        $needed_angeltype = NeededAngeltype_by_Shift_and_Angeltype($shift, $angeltype);
        if (empty($needed_angeltype)) {
            continue;
        }

        $shift_entries = $shift->shiftEntries()
            ->where('angel_type_id', $angeltype->id)
            ->get();
        $needed_angeltype = (new AngelType())->forceFill($needed_angeltype);

        $angeltype_signup_state = Shift_signup_allowed(
            $user,
            $shift,
            $angeltype,
            null,
            $user_shifts,
            $needed_angeltype,
            $shift_entries
        );
        $shift_signup_state->combineWith($angeltype_signup_state);
        $angeltype->shift_signup_state = $angeltype_signup_state;
    }

    return [
        htmlspecialchars($shift->shiftType->name),
        Shift_view($shift, $shifttype, $location, $angeltypes, $shift_signup_state),
    ];
}

/**
 * @return array
 */
function shifts_controller()
{
    $request = request();
    if (!$request->has('action')) {
        throw_redirect(url('/user-shifts'));
    }

    return match ($request->input('action')) {
        'view' => shift_controller(),
        'next' => shift_next_controller(), // throws redirect
        default => throw_redirect(url('/')),
    };
}

/**
 * Redirects the user to his next shift.
 */
function shift_next_controller()
{
    if (!auth()->can('user_shifts')) {
        throw_redirect(url('/'));
    }

    $upcoming_shifts = ShiftEntries_upcoming_for_user(auth()->user());

    if (!$upcoming_shifts->isEmpty()) {
        throw_redirect(shift_link($upcoming_shifts[0]->shift));
    }

    throw_redirect(url('/user-shifts'));
}
