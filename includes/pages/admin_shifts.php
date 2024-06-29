<?php

use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @return string
 */
function admin_shifts_title()
{
    return __('Create shifts');
}

/**
 * Assistent zum Anlegen mehrerer neuer Schichten
 *
 * @return string
 */
function admin_shifts()
{
    $valid = true;
    $request = request();
    $session = session();
    $start = Carbon::createFromDateTime(date('Y-m-d') . 'T00:00');
    $end = $start;
    $mode = 'multi';
    $angelmode = 'manually';
    $length = '';
    $change_hours = [];
    $title = '';
    $shifttype_id = null;
    $description = null;
    // When true: creates a shift beginning at the last shift change hour and ending at the first shift change hour
    $shift_over_midnight = true;

    // Locations laden
    $locations = Location::orderBy('name')->get();
    $no_locations = $locations->isEmpty();
    $location_array = $locations->pluck('name', 'id')->toArray();

    // Load angeltypes
    /** @var AngelType[]|Collection $types */
    $types = AngelType::all();
    $no_angeltypes = $types->isEmpty();
    $needed_angel_types = [];
    foreach ($types as $type) {
        $needed_angel_types[$type->id] = 0;
    }

    // Load shift types
    /** @var ShiftType[]|Collection $shifttypes_source */
    $shifttypes_source = ShiftType::all();
    $no_shifttypes = $shifttypes_source->isEmpty();
    $shifttypes = [];
    foreach ($shifttypes_source as $shifttype) {
        $shifttypes[$shifttype->id] = $shifttype->name;
    }

    if ($request->has('preview') || $request->has('back')) {
        if ($request->has('shifttype_id')) {
            $shifttype = ShiftType::find($request->input('shifttype_id'));
            if (empty($shifttype)) {
                $valid = false;
                error(__('Please select a shift type.'));
            } else {
                $shifttype_id = $request->input('shifttype_id');
            }
        } else {
            $valid = false;
            error(__('Please select a shift type.'));
        }

        // Name/Bezeichnung der Schicht, darf leer sein
        $title = substr(strip_request_item('title'), 0, 255);

        // Beschreibung der Schicht, darf leer sein
        $description = strip_request_item_nl('description');

        // Auswahl der sichtbaren Locations für die Schichten
        if (
            $request->has('lid')
            && preg_match('/^\d+$/', $request->input('lid'))
            && isset($location_array[$request->input('lid')])
        ) {
            $lid = $request->input('lid');
        } else {
            $valid = false;
            $lid = $locations->first()?->id ?? 0;
            error(__('Please select a location.'));
        }

        if ($request->has('start') && $tmp = Carbon::createFromDateTime($request->input('start'))) {
            $start = $tmp;
        } else {
            $valid = false;
            error(__('Please select a start time.'));
        }

        if ($request->has('end') && $tmp = Carbon::createFromDateTime($request->input('end'))) {
            $end = $tmp;
        } else {
            $valid = false;
            error(__('Please select an end time.'));
        }

        if ($start >= $end) {
            $valid = false;
            error(__('The shifts end has to be after its start.'));
        }

        if ($request->has('mode')) {
            if ($request->input('mode') == 'single') {
                $mode = 'single';
            } elseif ($request->input('mode') == 'multi') {
                if ($request->has('length') && preg_match('/^\d+$/', trim($request->input('length')))) {
                    $mode = 'multi';
                    $length = trim($request->input('length'));
                } else {
                    $valid = false;
                    error(__('Please enter a shift duration in minutes.'));
                }
            } elseif ($request->input('mode') == 'variable') {
                if (
                    $request->has('change_hours')
                    && preg_match(
                        '/^(\d{1,2}(:\d{2})?(,|$))+$/',
                        trim(str_replace(' ', '', $request->input('change_hours')))
                    )
                ) {
                    $mode = 'variable';
                    $change_hours = array_map(
                        'trim',
                        explode(',', $request->input('change_hours'))
                    );
                    // Fehlende Minutenangaben ergänzen, 24 Uhr -> 00 Uhr
                    array_walk($change_hours, function (&$value) use ($valid) {
                        // Add minutes
                        if (!preg_match('/^(\d{1,2}):\d{2}$/', $value)) {
                            $value .= ':00';
                        }
                        // Add 0 before low hours
                        if (preg_match('/^\d:\d{2}$/', $value)) {
                            $value = '0' . $value;
                        }
                        // Fix 24:00
                        if ($value == '24:00') {
                            $value = '00:00';
                        }
                    });
                    // Ensure valid time in change hours
                    foreach ($change_hours as $change_hour) {
                        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $change_hour)) {
                            $valid = false;
                            error(sprintf(__('Please validate the change hour %s. It should be between 00:00 and 24:00.'), $change_hour));
                        }
                    }
                    $change_hours = array_unique($change_hours);
                } else {
                    $valid = false;
                    error(__('Please split the shift-change hours by colons.'));
                }
                $shift_over_midnight = $request->has('shift_over_midnight')
                    && $request->input('shift_over_midnight') != 'false';
            }
        } else {
            $valid = false;
            error(__('Please select a mode.'));
        }

        if ($request->has('angelmode')) {
            if ($request->input('angelmode') == 'shift_type') {
                $angelmode = 'shift_type';
            } elseif ($request->input('angelmode') == 'location') {
                $angelmode = 'location';
            } elseif ($request->input('angelmode') == 'manually') {
                foreach ($types as $type) {
                    if (preg_match('/^\d+$/', trim($request->input('angeltype_count_' . $type->id, 0)))) {
                        $needed_angel_types[$type->id] = trim($request->input('angeltype_count_' . $type->id, 0));
                    } else {
                        $valid = false;
                        error(sprintf(__('Please check the needed angels for team %s.'), $type->name));
                    }
                }
            } else {
                $valid = false;
                error(__('Please select a mode for needed angels.'));
            }

            if (
                $angelmode == 'manually' && array_sum($needed_angel_types) == 0
                || $angelmode == 'location' && !NeededAngelType::whereLocationId($lid)
                    ->where('count', '>', '0')
                    ->count()
                || $angelmode == 'shift_type' && !NeededAngelType::whereShiftTypeId($shifttype_id)
                    ->where('count', '>', '0')
                    ->count()
            ) {
                $valid = false;
                error(__('There are 0 angels needed. Please enter the amounts of needed angels.'));
            }
        } else {
            $valid = false;
            error(__('Please select needed angels.'));
        }

        // Beim Zurück-Knopf das Formular zeigen
        if ($request->has('back')) {
            $valid = false;
        }

        // Alle Eingaben in Ordnung
        if ($valid) {
            if ($angelmode == 'shift_type') {
                $needed_angel_types = NeededAngelType::whereShiftTypeId($shifttype_id)
                        ->pluck('count', 'angel_type_id')
                        ->toArray() + $needed_angel_types;
            } elseif ($angelmode == 'location') {
                $needed_angel_types = NeededAngelType::whereLocationId($lid)
                        ->pluck('count', 'angel_type_id')
                        ->toArray() + $needed_angel_types;
            }

            $shifts = [];
            if ($mode == 'single') {
                $shifts[] = [
                    'start'         => $start,
                    'end'           => $end,
                    'location_id'   => $lid,
                    'title'         => $title,
                    'shift_type_id' => $shifttype_id,
                    'description'   => $description,
                ];
            } elseif ($mode == 'multi') {
                $shift_start = $start;
                do {
                    $shift_end = (clone $shift_start)->addSeconds((int) $length * 60);

                    if ($shift_end > $end) {
                        $shift_end = $end;
                    }
                    if ($shift_start >= $shift_end) {
                        break;
                    }

                    $shifts[] = [
                        'start'         => $shift_start,
                        'end'           => $shift_end,
                        'location_id'   => $lid,
                        'title'         => $title,
                        'shift_type_id' => $shifttype_id,
                        'description'   => $description,
                    ];

                    $shift_start = $shift_end;
                } while ($shift_end < $end);
            } elseif ($mode == 'variable') {
                // Alle Tage durchgehen
                $end_day = Carbon::createFromDatetime($end->format('Y-m-d') . ' 00:00');
                $day = Carbon::createFromDatetime($start->format('Y-m-d') . ' 00:00');
                do {
                    // Alle Schichtwechselstunden durchgehen
                    for ($i = 0; $i < count($change_hours); $i++) {
                        $start_hour = $change_hours[$i];
                        if (isset($change_hours[$i + 1])) {
                            // Normales Intervall zwischen zwei Schichtwechselstunden
                            $end_hour = $change_hours[$i + 1];
                        } elseif ($shift_over_midnight && $day != $end_day) {
                            // Letzte Schichtwechselstunde: Wenn eine 24h Abdeckung gewünscht ist,
                            // hier die erste Schichtwechselstunde als Ende einsetzen
                            $end_hour = $change_hours[0];
                        } else {
                            // Letzte Schichtwechselstunde: Keine Schicht erstellen
                            break;
                        }

                        $interval_start = Carbon::createFromDatetime($day->format('Y-m-d') . ' ' . $start_hour);
                        if (str_replace(':', '', $end_hour) < str_replace(':', '', $start_hour)) {
                            // Endstunde kleiner Startstunde? Dann sind wir im nächsten Tag gelandet
                            $interval_end = Carbon::createFromDatetime(date('Y-m-d', $day->timestamp + 36 * 60 * 60) . ' ' . $end_hour);
                        } else {
                            // Endstunde ist noch im selben Tag
                            $interval_end = Carbon::createFromDatetime($day->format('Y-m-d', $day) . ' ' . $end_hour);
                        }

                        // Liegt das Intervall vor dem Startzeitpunkt -> Überspringen
                        if ($interval_end <= $start) {
                            continue;
                        }

                        // Liegt das Intervall nach dem Endzeitpunkt -> Überspringen
                        if ($interval_start >= $end) {
                            continue;
                        }

                        // Liegt nur der Schichtstart vor dem Startzeitpunkt -> Startzeitpunkt übernehmen
                        if ($interval_start < $start) {
                            $interval_start = $start;
                        }

                        // Liegt nur das Schichtende nach dem Endzeitpunkt -> Endzeitpunkt übernehmen
                        if ($interval_end > $end) {
                            $interval_end = $end;
                        }

                        // Intervall für Schicht hinzufügen
                        $shifts[] = [
                            'start'         => $interval_start,
                            'end'           => $interval_end,
                            'location_id'   => $lid,
                            'title'         => $title,
                            'shift_type_id' => $shifttype_id,
                            'description'   => $description,
                        ];
                    }

                    $day = Carbon::createFromDatetime(date('Y-m-d', $day->timestamp + 36 * 60 * 60) . ' 00:00');
                } while ($day <= $end_day);

                usort($shifts, function ($a, $b) {
                    return $a['start'] < $b['start'] ? -1 : 1;
                });
            }

            $shifts_table = [];
            foreach ($shifts as $shift) {
                $shiftType = $shifttypes_source->find($shift['shift_type_id']);
                $location = $locations->find($shift['location_id']);

                /** @var Carbon $start */
                $start = $shift['start'];
                /** @var Carbon $end */
                $end = $shift['end'];
                $shifts_table_entry = [
                    'timeslot'      =>
                        icon('clock-history') . ' '
                        . $start->format(__('general.datetime'))
                        . ' - '
                        . '<span title="' . $end->format(__('general.date')) . '">'
                        . $end->format(__('H:i'))
                        . '</span>'
                        . ', ' . round($end->copy()->diffInMinutes($start) / 60, 2) . 'h'
                        . '<br>'
                        . location_name_render($location),
                    'title'         =>
                        htmlspecialchars($shiftType->name)
                        . ($shift['title'] ? '<br />' . htmlspecialchars($shift['title']) : ''),
                    'needed_angels' => '',
                ];
                foreach ($types as $type) {
                    if (isset($needed_angel_types[$type->id]) && $needed_angel_types[$type->id] > 0) {
                        $shifts_table_entry['needed_angels'] .= '<b>' . AngelType_name_render($type) . ':</b> '
                            . $needed_angel_types[$type->id] . '<br />';
                    }
                }
                $shifts_table[] = $shifts_table_entry;
            }

            // Fürs Anlegen zwischenspeichern:
            $session->set('admin_shifts_shifts', $shifts);
            $session->set('admin_shifts_types', $needed_angel_types);

            $previousEntries = [];
            foreach ($needed_angel_types as $type_id => $count) {
                $previousEntries['angeltype_count_' . $type_id] = $count;
            }

            // Number of Shifts that will be created (if over 100 its danger-red)
            $shiftsCount = count($shifts_table);
            $shiftsCreationHint = __('Number of shifts: %s', [$shiftsCount]);
            if ($shiftsCount >= 100) {
                $shiftsCreationHint = '<span class="text-danger">' . $shiftsCreationHint . '</span>';
            }

            // Save as previous state to be able to reuse it
            $previousEntries += [
                'shifttype_id' => $shifttype_id,
                'description' => $description,
                'title' => $title,
                'lid' => $lid,
                'start' => $request->input('start'),
                'end' => $request->input('end'),
                'mode' => $mode,
                'length' => $length,
                'change_hours' => implode(', ', $change_hours),
                'angelmode' => $angelmode,
                'shift_over_midnight' => $shift_over_midnight ? 'true' : 'false',
            ];
            $session->set('admin_shifts_previous', $previousEntries);

            $hidden_types = '';
            foreach ($previousEntries as $name => $value) {
                $hidden_types .= form_hidden($name, $value);
            }

            return page_with_title(__('form.preview'), [
                form([
                    $hidden_types,
                    form_submit('back', icon('chevron-left') . __('general.back')),
                    $shiftsCreationHint,
                    table([
                        'timeslot'      => __('Time and location'),
                        'title'         => __('Type and title'),
                        'needed_angels' => __('Needed angels'),
                    ], $shifts_table),
                    form_submit('submit', icon('save') . __('form.save')),
                ]),
            ]);
        }
    } elseif ($request->hasPostData('submit')) {
        if (
            !is_array($session->get('admin_shifts_shifts'))
            || !is_array($session->get('admin_shifts_types'))
        ) {
            throw_redirect(url('/admin-shifts'));
        }

        $transactionId = Str::uuid();
        foreach ($session->get('admin_shifts_shifts', []) as $shift) {
            $shift = new Shift($shift);
            $shift->url = '';
            $shift->transaction_id = $transactionId;
            $shift->createdBy()->associate(auth()->user());
            $shift->save();

            $needed_angel_types_info = [];
            foreach ($session->get('admin_shifts_types', []) as $type_id => $count) {
                $angel_type_source = AngelType::find($type_id);
                if (!empty($angel_type_source) && $count > 0) {
                    $neededAngelType = new NeededAngelType();
                    $neededAngelType->shift()->associate($shift);
                    $neededAngelType->angelType()->associate($angel_type_source);
                    $neededAngelType->count = $count;
                    $neededAngelType->save();

                    $needed_angel_types_info[] = $angel_type_source->name . ': ' . $count;
                }
            }

            engelsystem_log(
                'Shift created: ' . $shifttypes[$shift->shift_type_id]
                . ' (' . $shift->id . ')'
                . ' with title ' . $shift->title
                . ' and description ' . $shift->description
                . ' from ' . $shift->start->format('Y-m-d H:i')
                . ' to ' . $shift->end->format('Y-m-d H:i')
                . ' in ' . $shift->location->name
                . ' with angel types: ' . join(', ', $needed_angel_types_info)
                . ', transaction: ' . $transactionId
            );
        }

        success(__('Shifts created.'));
        throw_redirect(url('/admin-shifts'));
    } else {
        $session->remove('admin_shifts_shifts');
        $session->remove('admin_shifts_types');
    }

    $lid = null;
    if ($request->has('lid')) {
        $lid = $request->input('lid');
    }
    $angel_types = '';
    foreach ($types as $type) {
        $angel_types .= '<div class="col-sm-6 col-md-8 col-lg-6 col-xl-4 col-xxl-3">'
            . form_spinner(
                'angeltype_count_' . $type->id,
                htmlspecialchars($type->name),
                $needed_angel_types[$type->id],
                [
                    'radio-name'  => 'angelmode',
                    'radio-value' => 'manually',
                ]
            )
            . '</div>';
    }

    $link = button(url('/user-shifts'), icon('chevron-left'), 'btn-sm', '', __('general.back'));
    $reset = '';
    if ($session->has('admin_shifts_previous')) {
        $reset = form_submit(
            'back',
            icon('arrow-counterclockwise'),
            '',
            false,
            'link',
            __('Reset to previous state')
        );
        foreach ($session->get('admin_shifts_previous', []) as $name => $value) {
            $reset .= form_hidden($name, $value);
        }
    }

    return page_with_title(
        $link . ' ' . admin_shifts_title() . ' ' . sprintf(
            '<a href="%s">%s</a>',
            url('/admin/shifts/history'),
            icon('clock-history')
        ) . form([$reset], '', 'display:inline'),
        [
            $no_locations ? warning(__('admin_shifts.no_locations')) : '',
            $no_shifttypes ? warning(__('admin_shifts.no_shifttypes')) : '',
            $no_angeltypes ? warning(__('admin_shifts.no_angeltypes')) : '',
            msg(),
            form([
                div('row', [
                    div('col-md-6 col-xl-5', [
                        form_select('shifttype_id', __('Shifttype'), $shifttypes, $shifttype_id),
                        form_text('title', __('title.title'), $title, false, 255),
                        form_select('lid', __('Location'), $location_array, $lid),
                    ]),
                    div('col-md-6 col-xl-7', [
                        form_textarea('description', __('Additional description'), $description),
                        __('This description is for single shifts, otherwise please use the description in shift type.'),
                    ]),
                ]),
                div('row', [
                    div('col-md-6 col-xl-5', [
                        div('row', [
                            div('col-lg-6', [
                                form_datetime(
                                    'start',
                                    __('shifts.start'),
                                    $request->has('start')
                                        ? Carbon::createFromDatetime($request->input('start'))
                                        : $start
                                ),
                            ]),
                            div('col-lg-6', [
                                form_datetime(
                                    'end',
                                    __('shifts.end'),
                                    $request->has('end')
                                        ? Carbon::createFromDatetime($request->input('end'))
                                        : $end
                                ),
                            ]),
                        ]),
                        form_info(__('Mode')),
                        form_radio('mode', __('Create one shift'), $mode == 'single', 'single'),
                        form_radio('mode', __('Create multiple shifts'), $mode == 'multi', 'multi'),
                        form_text(
                            'length',
                            __('Length (in minutes)'),
                            $request->has('length')
                                ? $request->input('length')
                                : '120',
                            false,
                            null,
                            null,
                            '',
                            [
                                'radio-name'  => 'mode',
                                'radio-value' => 'multi',
                            ]
                        ),
                        form_radio(
                            'mode',
                            __('Create multiple shifts with variable length'),
                            $mode == 'variable',
                            'variable'
                        ),
                        form_text(
                            'change_hours',
                            __('Shift change hours'),
                            $request->has('change_hours')
                                ? ($change_hours ? implode(', ', $change_hours) : $request->input('change_hours'))
                                : '00, 04, 08, 10, 12, 14, 16, 18, 20, 22',
                            false,
                            null,
                            null,
                            '',
                            [
                                'radio-name'  => 'mode',
                                'radio-value' => 'variable',
                            ]
                        ),
                        form_checkbox(
                            'shift_over_midnight',
                            __('Create a shift over midnight.'),
                            $shift_over_midnight
                        ),
                    ]),
                    div('col-md-6 col-xl-7', [
                        form_info(__('Needed angels')),
                        form_radio(
                            'angelmode',
                            __('Copy needed angels from shift type settings'),
                            $angelmode == 'shift_type',
                            'shift_type'
                        ),
                        form_radio(
                            'angelmode',
                            __('Copy needed angels from location settings'),
                            $angelmode == 'location',
                            'location'
                        ),
                        form_radio(
                            'angelmode',
                            __('The following angels are needed'),
                            $angelmode == 'manually',
                            'manually'
                        ),
                        div('row', [
                            $angel_types,
                        ]),
                    ]),
                ]),
                form_submit('preview', icon('eye') . __('form.preview'), 'btn-info'),
            ]),
        ]
    );
}
