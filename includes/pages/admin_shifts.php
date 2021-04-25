<?php

use Engelsystem\Database\DB;
use Engelsystem\Models\Room;

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
    $start = parse_date('Y-m-d H:i', date('Y-m-d') . ' 00:00');
    $end = $start;
    $mode = 'single';
    $angelmode = 'manually';
    $length = '';
    $change_hours = [];
    $title = '';
    $shifttype_id = null;
    // When true: creates a shift beginning at the last shift change hour and ending at the first shift change hour
    $shift_over_midnight = true;

    // Locations laden
    $rooms = Rooms();
    $room_array = [];
    foreach ($rooms as $room) {
        $room_array[$room->id] = $room->name;
    }

    // Engeltypen laden
    $types = DB::select('SELECT * FROM `AngelTypes` ORDER BY `name`');
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

        if ($request->has('start') && $tmp = parse_date('Y-m-d H:i', $request->input('start'))) {
            $start = $tmp;
        } else {
            $valid = false;
            error(__('Please select a start time.'));
        }

        if ($request->has('end') && $tmp = parse_date('Y-m-d H:i', $request->input('end'))) {
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
                $angelmode = 'manually';
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
                $needed_angel_types_location = DB::select(
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
                    'shifttype_id' => $shifttype_id
                ];
            } elseif ($mode == 'multi') {
                $shift_start = (int)$start;
                do {
                    $shift_end = $shift_start + (int)$length * 60;

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
                        'shifttype_id' => $shifttype_id
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

                // Chronologisch absteigend sortieren (WHY???)
                usort($change_hours, function ($a, $b) {
                    return str_replace(':', '', $a) > str_replace(':', '', $b) ? -1 : 1;
                });

                // Start-Tag
                $day = parse_date('Y-m-d H:i', date('Y-m-d', $start) . ' 00:00');

                $change_index = 0;
                // Ersten/nächsten passenden Schichtwechsel suchen
                foreach ($change_hours as $i => $change_time) {
                    list($change_hour, $change_minute) = explode(':', $change_time);

                    $shift_end = $day + $change_hour * 60 * 60 + $change_minute * 60;
                    if ($start < $shift_end) {
                        $change_index = $i;
                    } elseif ($start == $shift_end) {
                        // Start trifft Schichtwechsel
                        $change_index = ($i + count($change_hours) - 1) % count($change_hours);
                        break;
                    } else {
                        break;
                    }
                }

                $shift_start = $start;
                do {
                    $day = parse_date('Y-m-d H:i', date('Y-m-d', $shift_start) . ' 00:00');

                    list($change_hour, $change_minute) = explode(':', $change_hours[$change_index]);

                    $shift_end = $day + $change_hour * 60 * 60 + $change_minute * 60;

                    if ($shift_end > $end) {
                        $shift_end = $end;
                    }
                    if ($shift_start >= $shift_end) {
                        $shift_end += 24 * 60 * 60;
                        if ($shift_end > $end) {
                            $shift_end = $end;
                        }
                    }

                    if($shift_over_midnight || $day == parse_date('Y-m-d H:i', date('Y-m-d', $shift_end) . ' 00:00')) {
                        $shifts[] = [
                            'start'        => $shift_start,
                            'end'          => $shift_end,
                            'RID'          => $rid,
                            'title'        => $title,
                            'shifttype_id' => $shifttype_id
                        ];
                    }

                    $shift_start = $shift_end;
                    $change_index--;
                    if($change_index < 0) {
                        $change_index = count($change_hours) - 1;
                    }
                } while ($shift_end < $end);
            }

            $shifts_table = [];
            foreach ($shifts as $shift) {
                $shifts_table_entry = [
                    'timeslot'      =>
                        '<span class="glyphicon glyphicon-time"></span> '
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
                    form_hidden('title', $title),
                    form_hidden('rid', $rid),
                    form_hidden('start', date('Y-m-d H:i', $start)),
                    form_hidden('end', date('Y-m-d H:i', $end)),
                    form_hidden('mode', $mode),
                    form_hidden('length', $length),
                    form_hidden('change_hours', implode(', ', $change_hours)),
                    form_hidden('angelmode', $angelmode),
                    form_hidden('shift_over_midnight', $shift_over_midnight ? 'true' : 'false'),
                    form_submit('back', glyph('menu-left') . __('back')),
                    table([
                        'timeslot'      => __('Time and location'),
                        'title'         => __('Type and title'),
                        'needed_angels' => __('Needed angels')
                    ], $shifts_table),
                    form_submit('submit', glyph('floppy-disk') . __('Save'))
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

        foreach ($session->get('admin_shifts_shifts', []) as $shift) {
            $shift['URL'] = null;
            $shift_id = Shift_create($shift);

            engelsystem_log(
                'Shift created: ' . $shifttypes[$shift['shifttype_id']]
                . ' with title ' . $shift['title']
                . ' from ' . date('Y-m-d H:i', $shift['start'])
                . ' to ' . date('Y-m-d H:i', $shift['end'])
            );

            $needed_angel_types_info = [];
            foreach ($session->get('admin_shifts_types', []) as $type_id => $count) {
                $angel_type_source = DB::selectOne('
                    SELECT *
                    FROM `AngelTypes`
                    WHERE `id` = ?
                    LIMIT 1', [$type_id]);

                if (!empty($angel_type_source)) {
                    DB::insert('
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

    return page_with_title(admin_shifts_title(), [
        msg(),
        form([
            form_select('shifttype_id', __('Shifttype'), $shifttypes, $shifttype_id),
            form_text('title', __('Title'), $title),
            form_select('rid', __('Room'), $room_array, $rid),
            div('row', [
                div('col-md-6', [
                    form_datetime('start', __('Start'), $start),
                    form_datetime('end', __('End'), $end),
                    form_info(__('Mode'), ''),
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
                    form_info(__('Needed angels'), ''),
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
            form_submit('preview', glyph('search') . __('Preview'))
        ])
    ]);
}
