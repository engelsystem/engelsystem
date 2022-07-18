<?php

use Engelsystem\Database\Db;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Models\Room;
use Engelsystem\Models\User\User;
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
    $start = Carbon::createTimestampFromDatetime(date('Y-m-d') . 'T00:00');
    $end = $start;
    $mode = '';
    $angelmode = 'manually';
    $length = '';
    $change_hours = [];
    $title = '';
    $shifttype_id = null;
    $description = null;
    // When true: creates a shift beginning at the last shift change hour and ending at the first shift change hour
    $shift_over_midnight = true;

    // Locations laden
    $rooms = Rooms();
    $room_array = [];
    foreach ($rooms as $room) {
        $room_array[$room->id] = $room->name;
    }

    // Engeltypen laden
    $types = Db::select('SELECT * FROM `AngelTypes` ORDER BY `name`');
    $needed_angel_types = [];
    foreach ($types as $type) {
        $needed_angel_types[$type['id']] = 0;
    }

    // Load shift types
    $shifttypes_source = ShiftTypes();
    $shifttypes = [];
    foreach ($shifttypes_source as $shifttype) {
        $shifttypes[$shifttype['id']] = $shifttype['name'];
    }

    if ($request->has('preview') || $request->has('back')) {
        if ($request->has('shifttype_id')) {
            $shifttype = ShiftType($request->input('shifttype_id'));
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
        $title = strip_request_item('title');

        // Beschreibung der Schicht, darf leer sein
        $description = strip_request_item_nl('description');

        // Auswahl der sichtbaren Locations für die Schichten
        if (
            $request->has('rid')
            && preg_match('/^\d+$/', $request->input('rid'))
            && isset($room_array[$request->input('rid')])
        ) {
            $rid = $request->input('rid');
        } else {
            $valid = false;
            $rid = $rooms->first()->id;
            error(__('Please select a location.'));
        }

        if ($request->has('start') && $tmp = Carbon::createTimestampFromDatetime($request->input('start'))) {
            $start = $tmp;
        } else {
            $valid = false;
            error(__('Please select a start time.'));
        }

        if ($request->has('end') && $tmp = Carbon::createTimestampFromDatetime($request->input('end'))) {
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
            if ($request->input('angelmode') == 'location') {
                $angelmode = 'location';
            } elseif ($request->input('angelmode') == 'manually') {
                foreach ($types as $type) {
                    if (preg_match('/^\d+$/', trim($request->input('type_' . $type['id'], 0)))) {
                        $needed_angel_types[$type['id']] = trim($request->input('type_' . $type['id'], 0));
                    } else {
                        $valid = false;
                        error(sprintf(__('Please check the needed angels for team %s.'), $type['name']));
                    }
                }

                if (array_sum($needed_angel_types) == 0) {
                    $valid = false;
                    error(__('There are 0 angels needed. Please enter the amounts of needed angels.'));
                }
            } else {
                $valid = false;
                error(__('Please select a mode for needed angels.'));
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
            if ($angelmode == 'location') {
                $needed_angel_types = [];
                $needed_angel_types_location = Db::select(
                    '
                        SELECT `angel_type_id`, `count`
                        FROM `NeededAngelTypes`
                        WHERE `room_id`=?
                    ',
                    [$rid]
                );
                foreach ($needed_angel_types_location as $type) {
                    $needed_angel_types[$type['angel_type_id']] = $type['count'];
                }
            }

            $shifts = [];
            if ($mode == 'single') {
                $shifts[] = [
                    'start'        => $start,
                    'end'          => $end,
                    'RID'          => $rid,
                    'title'        => $title,
                    'shifttype_id' => $shifttype_id,
                    'description'  => $description,
                ];
            } elseif ($mode == 'multi') {
                $shift_start = (int) $start;
                do {
                    $shift_end = $shift_start + (int) $length * 60;

                    if ($shift_end > $end) {
                        $shift_end = $end;
                    }
                    if ($shift_start >= $shift_end) {
                        break;
                    }

                    $shifts[] = [
                        'start'        => $shift_start,
                        'end'          => $shift_end,
                        'RID'          => $rid,
                        'title'        => $title,
                        'shifttype_id' => $shifttype_id,
                        'description'  => $description,
                    ];

                    $shift_start = $shift_end;
                } while ($shift_end < $end);
            } elseif ($mode == 'variable') {
                // Fehlende Minutenangaben ergänzen
                array_walk($change_hours, function (&$value) {
                    if (!preg_match('/^\d{1,2}:\d{2}$/', $value)) {
                        $value .= ':00';
                    }
                });

                // Alle Tage durchgehen
                $end_day = Carbon::createTimestampFromDatetime(date('Y-m-d', $end) . ' 00:00');
                $day = Carbon::createTimestampFromDatetime(date('Y-m-d', $start) . ' 00:00');
                do {
                    // Alle Schichtwechselstunden durchgehen
                    for($i = 0; $i < count($change_hours); $i++) {
                        $start_hour = $change_hours[$i];
                        if ($i < count($change_hours) - 1) {
                            // Normales Intervall zwischen zwei Schichtwechselstunden
                            $end_hour = $change_hours[$i+1];
                        } elseif ($shift_over_midnight) {
                            // Letzte Schichtwechselstunde: Wenn eine 24h Abdeckung gewünscht ist, hier die erste Schichtwechselstunde als Ende ensetzen
                            $end_hour = $change_hours[0];
                        } else {
                            // Letzte Schichtwechselstunde: Keine Schicht erstellen
                            break;
                        }

                        $interval_start = Carbon::createTimestampFromDatetime(date('Y-m-d', $day) . ' ' . $start_hour);
                        if (str_replace(':', '', $end_hour) < str_replace(':', '', $start_hour)) {
                            // Endstunde kleiner Startstunde? Dann sind wir im nächsten Tag gelandet
                            $interval_end = Carbon::createTimestampFromDatetime(date('Y-m-d', $day + 36 * 60 * 60) . ' ' . $end_hour);
                        } else {
                            // Endstunde ist noch im selben Tag
                            $interval_end = Carbon::createTimestampFromDatetime(date('Y-m-d', $day) . ' ' . $end_hour);
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
                            'start'        => $interval_start,
                            'end'          => $interval_end,
                            'RID'          => $rid,
                            'title'        => $title,
                            'shifttype_id' => $shifttype_id,
                            'description'  => $description
                        ];
                    }

                    $day = Carbon::createTimestampFromDatetime(date('Y-m-d', $day + 36 * 60 * 60) . ' 00:00');
                } while($day <= $end_day);

                usort($shifts, function ($a, $b) {
                    return $a['start'] < $b['start'] ? -1 : 1;
                });
            }

            $shifts_table = [];
            foreach ($shifts as $shift) {
                $shifts_table_entry = [
                    'timeslot'      =>
                        icon('clock') . ' '
                        . date('Y-m-d H:i', $shift['start'])
                        . ' - '
                        . date('H:i', $shift['end'])
                        . '<br />'
                        . Room_name_render(Room::find($shift['RID'])),
                    'title'         =>
                        ShiftType_name_render(ShiftType($shifttype_id))
                        . ($shift['title'] ? '<br />' . $shift['title'] : ''),
                    'needed_angels' => ''
                ];
                foreach ($types as $type) {
                    if (isset($needed_angel_types[$type['id']]) && $needed_angel_types[$type['id']] > 0) {
                        $shifts_table_entry['needed_angels'] .= '<b>' . AngelType_name_render($type) . ':</b> '
                            . $needed_angel_types[$type['id']] . '<br />';
                    }
                }
                $shifts_table[] = $shifts_table_entry;
            }

            // Fürs Anlegen zwischenspeichern:
            $session->set('admin_shifts_shifts', $shifts);
            $session->set('admin_shifts_types', $needed_angel_types);

            $hidden_types = '';
            foreach ($needed_angel_types as $type_id => $count) {
                $hidden_types .= form_hidden('type_' . $type_id, $count);
            }
            return page_with_title(__('Preview'), [
                form([
                    $hidden_types,
                    form_hidden('shifttype_id', $shifttype_id),
                    form_hidden('description', $description),
                    form_hidden('title', $title),
                    form_hidden('rid', $rid),
                    form_hidden('start', date('Y-m-d H:i', $start)),
                    form_hidden('end', date('Y-m-d H:i', $end)),
                    form_hidden('mode', $mode),
                    form_hidden('length', $length),
                    form_hidden('change_hours', implode(', ', $change_hours)),
                    form_hidden('angelmode', $angelmode),
                    form_hidden('shift_over_midnight', $shift_over_midnight ? 'true' : 'false'),
                    form_submit('back', icon('chevron-left') . __('back')),
                    table([
                        'timeslot'      => __('Time and location'),
                        'title'         => __('Type and title'),
                        'needed_angels' => __('Needed angels')
                    ], $shifts_table),
                    form_submit('submit', icon('save') . __('Save'))
                ])
            ]);
        }
    } elseif ($request->hasPostData('submit')) {
        if (
            !is_array($session->get('admin_shifts_shifts'))
            || !is_array($session->get('admin_shifts_types'))
        ) {
            throw_redirect(page_link_to('admin_shifts'));
        }

        $transactionId = Str::uuid();
        foreach ($session->get('admin_shifts_shifts', []) as $shift) {
            $shift['URL'] = null;
            $shift_id = Shift_create($shift, $transactionId);

            engelsystem_log(
                'Shift created: ' . $shifttypes[$shift['shifttype_id']]
                . ' with title ' . $shift['title']
                . ' with description ' . $shift['description']
                . ' from ' . date('Y-m-d H:i', $shift['start'])
                . ' to ' . date('Y-m-d H:i', $shift['end'])
                . ', transaction: ' . $transactionId
            );

            $needed_angel_types_info = [];
            foreach ($session->get('admin_shifts_types', []) as $type_id => $count) {
                $angel_type_source = Db::selectOne('
                    SELECT *
                    FROM `AngelTypes`
                    WHERE `id` = ?
                    LIMIT 1', [$type_id]);

                if (!empty($angel_type_source)) {
                    Db::insert('
                        INSERT INTO `NeededAngelTypes` (`shift_id`, `angel_type_id`, `count`)
                        VALUES (?, ?, ?)
                        ',
                        [
                            $shift_id,
                            $type_id,
                            $count
                        ]
                    );

                    if ($count > 0) {
                        $needed_angel_types_info[] = $angel_type_source['name'] . ': ' . $count;
                    }
                }
            }
            engelsystem_log('Shift needs following angel types: ' . join(', ', $needed_angel_types_info));
        }

        success('Schichten angelegt.');
        throw_redirect(page_link_to('admin_shifts'));
    } else {
        $session->remove('admin_shifts_shifts');
        $session->remove('admin_shifts_types');
    }

    $rid = null;
    if ($request->has('rid')) {
        $rid = $request->input('rid');
    }
    $angel_types = '';
    foreach ($types as $type) {
        $angel_types .= '<div class="col-md-4">' . form_spinner(
                'type_' . $type['id'],
                $type['name'],
                $needed_angel_types[$type['id']]
            )
            . '</div>';
    }

    return page_with_title(
        admin_shifts_title() . ' ' .  sprintf(
            '<a href="%s">%s</a>',
            page_link_to('admin_shifts_history'),
            icon('clock-history')
        ), [
        msg(),
        form([
            div('row',[
                div('col-md-6', [
                    form_select('shifttype_id', __('Shifttype'), $shifttypes, $shifttype_id),
                    form_text('title', __('Title'), $title),
                    form_select('rid', __('Room'), $room_array, $rid),
                ]),
                div('col-md-6', [
                    form_textarea('description', __('Additional description'), $description),
                    __('This description is for single shifts, otherwise please use the description in shift type.'),
                ]),
            ]),
            div('row', [
                div('col-md-6', [
                    form_datetime('start', __('Start'), $start),
                    form_datetime('end', __('End'), $end),
                    form_info(__('Mode')),
                    form_radio('mode', __('Create one shift'), $mode == 'single', 'single'),
                    form_radio('mode', __('Create multiple shifts'), $mode == 'multi', 'multi'),
                    form_text(
                        'length',
                        __('Length'),
                        $request->has('length')
                            ? $request->input('length')
                            : '120'
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
                            ? $request->input('change_hours')
                            : '00, 04, 08, 10, 12, 14, 16, 18, 20, 22'
                    ),
                    form_checkbox(
                        'shift_over_midnight',
                        __('Create a shift over midnight.'),
                        $shift_over_midnight
                    )
                ]),
                div('col-md-6', [
                    form_info(__('Needed angels')),
                    form_radio(
                        'angelmode',
                        __('Take needed angels from room settings'),
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
                        $angel_types
                    ])
                ])
            ]),
            form_submit('preview', icon('search') . __('Preview'))
        ])
    ]);
}

/**
 * @return string
 */
function admin_shifts_history_title(): string
{
    return __('Shifts history');
}

/**
 * Display shifts transaction history
 *
 * @return string
 */
function admin_shifts_history(): string
{
    if (!auth()->can('admin_shifts')) {
        throw new HttpForbidden();
    }

    $request = request();
    $transactionId = $request->postData('transaction_id');
    if ($request->hasPostData('delete') && $transactionId) {
        $shifts = Db::select('
            SELECT SID
            FROM Shifts
            WHERE transaction_id = ?
        ', [$transactionId]);

        engelsystem_log('Deleting ' . count($shifts) . ' shifts (transaction id ' . $transactionId . ')');

        foreach ($shifts as $shift) {
            $shift = Shift($shift['SID']);
            UserWorkLog_from_shift($shift);
            shift_delete($shift['SID']);

            engelsystem_log(
                'Deleted shift ' . $shift['name']
                . ' from ' . date('Y-m-d H:i', $shift['start'])
                . ' to ' . date('Y-m-d H:i', $shift['end'])
            );
        }

        success(sprintf(__('%s shifts deleted.'), count($shifts)));
        throw_redirect(page_link_to('admin_shifts_history'));
    }

    $shifts = Db::select('
        SELECT
            transaction_id,
            title,
            COUNT(SID) AS count,
            MIN(start) AS start,
            MAX(end) AS end,
            created_by_user_id AS user_id,
            created_at_timestamp AS created_at
        FROM Shifts
        WHERE transaction_id IS NOT NULL
        GROUP BY transaction_id
        ORDER BY transaction_id DESC
    ');

    foreach ($shifts as &$shift) {
        $shift['user'] = User_Nick_render(User::find($shift['user_id']));
        $shift['start'] = Carbon::createFromTimestamp($shift['start'])->format(__('Y-m-d H:i'));
        $shift['end'] = Carbon::createFromTimestamp($shift['end'])->format(__('Y-m-d H:i'));
        $shift['created_at'] = Carbon::createFromTimestamp($shift['created_at'])->format(__('Y-m-d H:i'));
        $shift['actions'] = form([
            form_hidden('transaction_id', $shift['transaction_id']),
            form_submit('delete', icon('trash') . __('delete all'), 'btn-sm', true, 'danger'),
        ]);
    }

    return page_with_title(admin_shifts_history_title(), [
        msg(),
        table([
            'transaction_id' => __('ID'),
            'title'          => __('Title'),
            'count'          => __('Count'),
            'start'          => __('Start'),
            'end'            => __('End'),
            'user'           => __('User'),
            'created_at'     => __('Created'),
            'actions'        => ''
        ], $shifts)
    ], true);
}
