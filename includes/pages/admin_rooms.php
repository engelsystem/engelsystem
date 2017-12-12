<?php
/**
 * @return string
 */
function admin_rooms_title()
{
    return _('Rooms');
}

/**
 * @return string
 */
function admin_rooms()
{
    $rooms_source = Rooms();
    $rooms = [];
    $request = request();

    foreach ($rooms_source as $room) {
        $rooms[] = [
            'name'           => Room_name_render($room),
            'from_frab' => glyph_bool($room['from_frab']),
            'map_url'         => glyph_bool(!empty($room['map_url'])),
            'actions'        => table_buttons([
                button(page_link_to('admin_rooms', ['show' => 'edit', 'id' => $room['RID']]), _('edit'), 'btn-xs'),
                button(page_link_to('admin_rooms', ['show' => 'delete', 'id' => $room['RID']]), _('delete'), 'btn-xs')
            ])
        ];
    }
    $room = null;

    if ($request->has('show')) {
        $msg = '';
        $name = '';
        $from_frab = false;
        $map_url = null;
        $description = null;
        $room_id = 0;

        $angeltypes_source = AngelTypes();
        $angeltypes = [];
        $angeltypes_count = [];
        foreach ($angeltypes_source as $angeltype) {
            $angeltypes[$angeltype['id']] = $angeltype['name'];
            $angeltypes_count[$angeltype['id']] = 0;
        }

        if (test_request_int('id')) {
            $room = Room($request->input('id'));
            if ($room == null) {
                redirect(page_link_to('admin_rooms'));
            }

            $room_id = $request->input('id');
            $name = $room['Name'];
            $from_frab = $room['from_frab'];
            $map_url = $room['map_url'];
            $description = $room['description'];

            $needed_angeltypes = NeededAngelTypes_by_room($room_id);
            foreach ($needed_angeltypes as $needed_angeltype) {
                $angeltypes_count[$needed_angeltype['angel_type_id']] = $needed_angeltype['count'];
            }
        }

        if ($request->input('show') == 'edit') {
            if ($request->has('submit')) {
                $valid = true;

                if ($request->has('name') && strlen(strip_request_item('name')) > 0) {
                    $result = Room_validate_name(strip_request_item('name'), $room_id);
                    if(!$result->isValid()) {
                        $valid = false;
                        $msg .= error(_('This name is already in use.'), true);
                    } else {
                        $name = $result->getValue();
                    }
                } else {
                    $valid = false;
                    $msg .= error(_('Please enter a name.'), true);
                }

                $from_frab = $request->has('from_frab');

                if ($request->has('map_url')) {
                    $map_url = strip_request_item('map_url');
                }

                if ($request->has('description')) {
                    $description= strip_request_item_nl('description');
                }

                foreach ($angeltypes as $angeltype_id => $angeltype) {
                    $angeltypes_count[$angeltype_id] = 0;
                    $queryKey = 'angeltype_count_' . $angeltype_id;
                    if (!$request->has($queryKey)) {
                        continue;
                    }

                    if (preg_match('/^\d{1,4}$/', $request->input($queryKey))) {
                        $angeltypes_count[$angeltype_id] = $request->input($queryKey);
                    } else {
                        $valid = false;
                        $msg .= error(sprintf(_('Please enter needed angels for type %s.'), $angeltype), true);
                    }
                }

                if ($valid) {
                    if (empty($room_id)) {
                        $room_id = Room_create($name, $from_frab, $map_url, $description);
                    } else {
                        Room_update($room_id, $name, $from_frab, $map_url, $description);
                    }

                    NeededAngelTypes_delete_by_room($room_id);
                    $needed_angeltype_info = [];
                    foreach ($angeltypes_count as $angeltype_id => $angeltype_count) {
                        $angeltype = AngelType($angeltype_id);
                        if ($angeltype != null) {
                            NeededAngelType_add(null, $angeltype_id, $room_id, $angeltype_count);
                            if($angeltype_count > 0) {
                                $needed_angeltype_info[] = $angeltype['name'] . ': ' . $angeltype_count;
                            }
                        }
                    }

                    engelsystem_log(
                        'Set needed angeltypes of room ' . $name
                        . ' to: ' . join(', ', $needed_angeltype_info)
                    );
                    success(_('Room saved.'));
                    redirect(page_link_to('admin_rooms'));
                }
            }
            $angeltypes_count_form = [];
            foreach ($angeltypes as $angeltype_id => $angeltype) {
                $angeltypes_count_form[] = div('col-lg-4 col-md-6 col-xs-6', [
                    form_spinner('angeltype_count_' . $angeltype_id, $angeltype, $angeltypes_count[$angeltype_id])
                ]);
            }

            return page_with_title(admin_rooms_title(), [
                buttons([
                    button(page_link_to('admin_rooms'), _('back'), 'back')
                ]),
                $msg,
                form([
                    div('row', [
                        div('col-md-6', [
                            form_text('name', _('Name'), $name),
                            form_checkbox('from_frab', _('Frab import'), $from_frab),
                            form_text('map_url', _('Map URL'), $map_url),
                            form_info('', _('The map url is used to display an iframe on the room page.')),
                            form_textarea('description', _('Description'), $description),
                            form_info('', _('Please use markdown for the description.')),
                        ]),
                        div('col-md-6', [
                            div('row', [
                                div('col-md-12', [
                                    form_info(_('Needed angels:'))
                                ]),
                                join($angeltypes_count_form)
                            ])
                        ])
                    ]),
                    form_submit('submit', _('Save'))
                ])
            ]);
        } elseif ($request->input('show') == 'delete') {
            if ($request->has('ack')) {
                Room_delete($room_id);

                engelsystem_log('Room deleted: ' . $name);
                success(sprintf(_('Room %s deleted.'), $name));
                redirect(page_link_to('admin_rooms'));
            }

            return page_with_title(admin_rooms_title(), [
                buttons([
                    button(page_link_to('admin_rooms'), _('back'), 'back')
                ]),
                sprintf(_('Do you want to delete room %s?'), $name),
                buttons([
                    button(
                        page_link_to('admin_rooms', ['show' => 'delete', 'id' => $room_id, 'ack' => 1]),
                        _('Delete'),
                        'delete btn-danger'
                    )
                ])
            ]);
        }
    }

    return page_with_title(admin_rooms_title(), [
        buttons([
            button(page_link_to('admin_rooms', ['show' => 'edit']), _('add'))
        ]),
        msg(),
        table([
            'name'           => _('Name'),
            'from_frab' => _('Frab import'),
            'map_url'         => _('Map'),
            'actions'        => ''
        ], $rooms)
    ]);
}
