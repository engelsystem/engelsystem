<?php

/**
 * @return string
 */
function admin_import_title()
{
    return __('Frab import');
}

/**
 * @return string
 */
function admin_import()
{
    global $rooms_import;
    $user = auth()->user();
    $html = '';
    $import_dir = __DIR__ . '/../../import';
    $request = request();

    $step = 'input';
    if (
        $request->has('step')
        && in_array($request->input('step'), [
            'input',
            'check',
            'import'
        ])
    ) {
        $step = $request->input('step');
    }

    try {
        $test_handle = @fopen($import_dir . '/tmp', 'w');
        fclose($test_handle);
        @unlink($import_dir . '/tmp');
    } catch (Exception $e) {
        error(__('Webserver has no write-permission on import directory.'));
    }

    $import_file = $import_dir . '/import_' . $user->id . '.xml';
    $shifttype_id = null;
    $add_minutes_start = 15;
    $add_minutes_end = 15;

    $shifttypes_source = ShiftTypes();
    $shifttypes = [];
    foreach ($shifttypes_source as $shifttype) {
        $shifttypes[$shifttype['id']] = $shifttype['name'];
    }

    switch ($step) {
        case 'input':
            $valid = false;

            if ($request->hasPostData('submit')) {
                $valid = true;

                if ($request->has('shifttype_id') && isset($shifttypes[$request->input('shifttype_id')])) {
                    $shifttype_id = $request->input('shifttype_id');
                } else {
                    $valid = false;
                    error(__('Please select a shift type.'));
                }

                $minutes_start = trim($request->input('add_minutes_start'));
                if ($request->has('add_minutes_start') && is_numeric($minutes_start)) {
                    $add_minutes_start = $minutes_start;
                } else {
                    $valid = false;
                    error(__('Please enter an amount of minutes to add to a talk\'s begin.'));
                }

                if ($request->has('add_minutes_end') && is_numeric(trim($request->input('add_minutes_end')))) {
                    $add_minutes_end = trim($request->input('add_minutes_end'));
                } else {
                    $valid = false;
                    error(__('Please enter an amount of minutes to add to a talk\'s end.'));
                }

                if (isset($_FILES['xcal_file']) && ($_FILES['xcal_file']['error'] == 0)) {
                    if (move_uploaded_file($_FILES['xcal_file']['tmp_name'], $import_file)) {
                        libxml_use_internal_errors(true);
                        if (simplexml_load_file($import_file) === false) {
                            $valid = false;
                            error(__('No valid xml/xcal file provided.'));
                            unlink($import_file);
                        }
                    } else {
                        $valid = false;
                        error(__('File upload went wrong.'));
                    }
                } else {
                    $valid = false;
                    error(__('Please provide some data.'));
                }
            }

            if ($valid) {
                redirect(
                    page_link_to('admin_import', [
                        'step'              => 'check',
                        'shifttype_id'      => $shifttype_id,
                        'add_minutes_end'   => $add_minutes_end,
                        'add_minutes_start' => $add_minutes_start,
                    ])
                );
            } else {
                $html .= div('well well-sm text-center', [
                        __('File Upload')
                        . mute(glyph('arrow-right'))
                        . mute(__('Validation'))
                        . mute(glyph('arrow-right'))
                        . mute(__('Import'))
                    ]) . div('row', [
                        div('col-md-offset-3 col-md-6', [
                            form([
                                form_info(
                                    '',
                                    __('This import will create/update/delete rooms and shifts by given FRAB-export file. The needed file format is xcal.')
                                ),
                                form_select('shifttype_id', __('Shifttype'), $shifttypes, $shifttype_id),
                                form_spinner('add_minutes_start', __('Add minutes to start'), $add_minutes_start),
                                form_spinner('add_minutes_end', __('Add minutes to end'), $add_minutes_end),
                                form_file('xcal_file', __('xcal-File (.xcal)')),
                                form_submit('submit', __('Import'))
                            ])
                        ])
                    ]);
            }
            break;

        case 'check':
            if (!file_exists($import_file)) {
                error(__('Missing import file.'));
                redirect(page_link_to('admin_import'));
            }

            if ($request->has('shifttype_id') && isset($shifttypes[$request->input('shifttype_id')])) {
                $shifttype_id = $request->input('shifttype_id');
            } else {
                error(__('Please select a shift type.'));
                redirect(page_link_to('admin_import'));
            }

            if ($request->has('add_minutes_start') && is_numeric(trim($request->input('add_minutes_start')))) {
                $add_minutes_start = trim($request->input('add_minutes_start'));
            } else {
                error(__('Please enter an amount of minutes to add to a talk\'s begin.'));
                redirect(page_link_to('admin_import'));
            }

            if ($request->has('add_minutes_end') && is_numeric(trim($request->input(('add_minutes_end'))))) {
                $add_minutes_end = trim($request->input('add_minutes_end'));
            } else {
                error(__('Please enter an amount of minutes to add to a talk\'s end.'));
                redirect(page_link_to('admin_import'));
            }

            list($rooms_new, $rooms_deleted) = prepare_rooms($import_file);
            list($events_new, $events_updated, $events_deleted) = prepare_events(
                $import_file,
                $shifttype_id,
                $add_minutes_start,
                $add_minutes_end
            );

            $html .= div(
                    'well well-sm text-center',
                    [
                        '<span class="text-success">' . __('File Upload') . glyph('ok-circle') . '</span>'
                        . mute(glyph('arrow-right'))
                        . __('Validation')
                        . mute(glyph('arrow-right'))
                        . mute(__('Import'))
                    ]
                )
                . form(
                    [
                        div('row', [
                            div('col-sm-6', [
                                '<h3>' . __('Rooms to create') . '</h3>',
                                table(__('Name'), $rooms_new)
                            ]),
                            div('col-sm-6', [
                                '<h3>' . __('Rooms to delete') . '</h3>',
                                table(__('Name'), $rooms_deleted)
                            ])
                        ]),
                        '<h3>' . __('Shifts to create') . '</h3>',
                        table([
                            'day'       => __('Day'),
                            'start'     => __('Start'),
                            'end'       => __('End'),
                            'shifttype' => __('Shift type'),
                            'title'     => __('Title'),
                            'room'      => __('Room')
                        ], shifts_printable($events_new, $shifttypes)),
                        '<h3>' . __('Shifts to update') . '</h3>',
                        table([
                            'day'       => __('Day'),
                            'start'     => __('Start'),
                            'end'       => __('End'),
                            'shifttype' => __('Shift type'),
                            'title'     => __('Title'),
                            'room'      => __('Room')
                        ], shifts_printable($events_updated, $shifttypes)),
                        '<h3>' . __('Shifts to delete') . '</h3>',
                        table([
                            'day'       => __('Day'),
                            'start'     => __('Start'),
                            'end'       => __('End'),
                            'shifttype' => __('Shift type'),
                            'title'     => __('Title'),
                            'room'      => __('Room')
                        ], shifts_printable($events_deleted, $shifttypes)),
                        form_submit('submit', __('Import'))
                    ],
                    page_link_to('admin_import', [
                        'step'              => 'import',
                        'shifttype_id'      => $shifttype_id,
                        'add_minutes_end'   => $add_minutes_end,
                        'add_minutes_start' => $add_minutes_start,
                    ])
                );
            break;

        case 'import':
            if (!file_exists($import_file)) {
                error(__('Missing import file.'));
                redirect(page_link_to('admin_import'));
            }

            if (!file_exists($import_file)) {
                redirect(page_link_to('admin_import'));
            }

            if ($request->has('shifttype_id') && isset($shifttypes[$request->input('shifttype_id')])) {
                $shifttype_id = $request->input('shifttype_id');
            } else {
                error(__('Please select a shift type.'));
                redirect(page_link_to('admin_import'));
            }

            if ($request->has('add_minutes_start') && is_numeric(trim($request->input('add_minutes_start')))) {
                $add_minutes_start = trim($request->input('add_minutes_start'));
            } else {
                error(__('Please enter an amount of minutes to add to a talk\'s begin.'));
                redirect(page_link_to('admin_import'));
            }

            if ($request->has('add_minutes_end') && is_numeric(trim($request->input('add_minutes_end')))) {
                $add_minutes_end = trim($request->input('add_minutes_end'));
            } else {
                error(__('Please enter an amount of minutes to add to a talk\'s end.'));
                redirect(page_link_to('admin_import'));
            }

            list($rooms_new, $rooms_deleted) = prepare_rooms($import_file);
            foreach ($rooms_new as $room) {
                $result = Room_create($room, true, null, null);
                $rooms_import[trim($room)] = $result;
            }
            foreach ($rooms_deleted as $room) {
                Room_delete_by_name($room);
            }

            list($events_new, $events_updated, $events_deleted) = prepare_events(
                $import_file,
                $shifttype_id,
                $add_minutes_start,
                $add_minutes_end
            );
            foreach ($events_new as $event) {
                Shift_create($event);
            }

            foreach ($events_updated as $event) {
                Shift_update_by_psid($event);
            }

            foreach ($events_deleted as $event) {
                Shift_delete_by_psid($event['PSID']);
            }

            engelsystem_log('Frab import done');

            unlink($import_file);

            $html .= div('well well-sm text-center', [
                    '<span class="text-success">' . __('File Upload') . glyph('ok-circle') . '</span>'
                    . mute(glyph('arrow-right'))
                    . '<span class="text-success">' . __('Validation') . glyph('ok-circle') . '</span>'
                    . mute(glyph('arrow-right'))
                    . '<span class="text-success">' . __('Import') . glyph('ok-circle') . '</span>'
                ]) . success(__('It\'s done!'), true);
            break;
        default:
            redirect(page_link_to('admin_import'));
    }

    return page_with_title(admin_import_title(), [
        msg(),
        $html
    ]);
}

/**
 * @param string $file
 * @return array
 */
function prepare_rooms($file)
{
    global $rooms_import;
    $data = read_xml($file);

    // Load rooms from db for compare with input
    $rooms = Rooms();
    // Contains rooms from db with from_frab==true
    $rooms_db = [];
    // Contains all rooms from db
    $rooms_db_all = [];
    // Contains all rooms from db and frab
    $rooms_import = [];
    foreach ($rooms as $room) {
        if ($room['from_frab']) {
            $rooms_db[] = $room['Name'];
        }
        $rooms_db_all[] = $room['Name'];
        $rooms_import[$room['Name']] = $room['RID'];
    }

    $events = $data->vcalendar->vevent;
    $rooms_frab = [];
    foreach ($events as $event) {
        $rooms_frab[] = (string)$event->location;
        if (!isset($rooms_import[trim($event->location)])) {
            $rooms_import[trim($event->location)] = trim($event->location);
        }
    }
    $rooms_frab = array_unique($rooms_frab);

    $rooms_new = array_diff($rooms_frab, $rooms_db_all);
    $rooms_deleted = array_diff($rooms_db, $rooms_frab);

    return [
        $rooms_new,
        $rooms_deleted
    ];
}

/**
 * @param string $file
 * @param int    $shifttype_id
 * @param int    $add_minutes_start
 * @param int    $add_minutes_end
 * @return array
 */
function prepare_events($file, $shifttype_id, $add_minutes_start, $add_minutes_end)
{
    global $rooms_import;
    $data = read_xml($file);

    $rooms = Rooms();
    $rooms_db = [];
    foreach ($rooms as $room) {
        $rooms_db[$room['Name']] = $room['RID'];
    }

    $events = $data->vcalendar->vevent;
    $shifts_pb = [];
    foreach ($events as $event) {
        $event_pb = $event->children('http://pentabarf.org');
        $event_id = trim($event_pb->{'event-id'});
        $shifts_pb[$event_id] = [
            'shifttype_id' => $shifttype_id,
            'start'        => parse_date("Ymd\THis", $event->dtstart) - $add_minutes_start * 60,
            'end'          => parse_date("Ymd\THis", $event->dtend) + $add_minutes_end * 60,
            'RID'          => $rooms_import[trim($event->location)],
            'title'        => trim($event->summary),
            'URL'          => trim($event->url),
            'PSID'         => $event_id
        ];
    }

    $shifts = Shifts_from_frab();
    $shifts_db = [];
    foreach ($shifts as $shift) {
        $shifts_db[$shift['PSID']] = $shift;
    }

    $shifts_new = [];
    $shifts_updated = [];
    foreach ($shifts_pb as $shift) {
        if (!isset($shifts_db[$shift['PSID']])) {
            $shifts_new[] = $shift;
        } else {
            $tmp = $shifts_db[$shift['PSID']];
            if (
                $shift['shifttype_id'] != $tmp['shifttype_id']
                || $shift['title'] != $tmp['title']
                || $shift['start'] != $tmp['start']
                || $shift['end'] != $tmp['end']
                || $shift['RID'] != $tmp['RID']
                || $shift['URL'] != $tmp['URL']
            ) {
                $shifts_updated[] = $shift;
            }
        }
    }

    $shifts_deleted = [];
    foreach ($shifts_db as $shift) {
        if (!isset($shifts_pb[$shift['PSID']])) {
            $shifts_deleted[] = $shift;
        }
    }

    return [
        $shifts_new,
        $shifts_updated,
        $shifts_deleted
    ];
}

/**
 * @param string $file
 * @return SimpleXMLElement
 */
function read_xml($file)
{
    global $xml_import;
    if (!isset($xml_import)) {
        libxml_use_internal_errors(true);
        $xml_import = simplexml_load_file($file);
    }
    return $xml_import;
}

/**
 * @param array $shifts
 * @param array $shifttypes
 * @return array
 */
function shifts_printable($shifts, $shifttypes)
{
    global $rooms_import;
    $rooms = array_flip($rooms_import);

    uasort($shifts, 'shift_sort');

    $shifts_printable = [];
    foreach ($shifts as $shift) {
        $shifts_printable[] = [
            'day'       => date('l, Y-m-d', $shift['start']),
            'start'     => date('H:i', $shift['start']),
            'shifttype' => ShiftType_name_render([
                'id'   => $shift['shifttype_id'],
                'name' => $shifttypes[$shift['shifttype_id']]
            ]),
            'title'     => shorten($shift['title']),
            'end'       => date('H:i', $shift['end']),
            'room'      => $rooms[$shift['RID']]
        ];
    }
    return $shifts_printable;
}

/**
 * @param array $shift_a
 * @param array $shift_b
 * @return int
 */
function shift_sort($shift_a, $shift_b)
{
    return ($shift_a['start'] < $shift_b['start']) ? -1 : 1;
}
