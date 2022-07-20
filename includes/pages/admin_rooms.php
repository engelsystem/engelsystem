<?php

use Engelsystem\Models\Room;

/**
 * @return string
 */
function admin_rooms_title()
{
    return __('Rooms');
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
            'name'      => Room_name_render($room),
            'map_url'   => icon_bool($room->map_url),
            'actions'   => table_buttons([
                button(
                    page_link_to('admin_rooms', ['show' => 'edit', 'id' => $room->id]),
                    __('edit'),
                    'btn-sm'
                ),
                button(
                    page_link_to('admin_rooms', ['show' => 'delete', 'id' => $room->id]),
                    __('delete'),
                    'btn-sm'
                )
            ])
        ];
    }

    $room = null;
    if ($request->has('show')) {
        $msg = '';
        $name = '';
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
            $room = Room::find($request->input('id'));
            if (!$room) {
                throw_redirect(page_link_to('admin_rooms'));
            }

            $room_id = $room->id;
            $name = $room->name;
            $map_url = $room->map_url;
            $description = $room->description;

            $needed_angeltypes = NeededAngelTypes_by_room($room_id);
            foreach ($needed_angeltypes as $needed_angeltype) {
                $angeltypes_count[$needed_angeltype['angel_type_id']] = $needed_angeltype['count'];
            }
        }

        if ($request->input('show') == 'edit') {
            if ($request->hasPostData('submit')) {
                $valid = true;

                if ($request->has('name') && strlen(strip_request_tags('name')) > 0) {
                    $result = Room_validate_name(strip_request_tags('name'), $room_id);
                    if (!$result->isValid()) {
                        $valid = false;
                        $msg .= error(__('This name is already in use.'), true);
                    } else {
                        $name = $result->getValue();
                    }
                } else {
                    $valid = false;
                    $msg .= error(__('Please enter a name.'), true);
                }

                if ($request->has('map_url')) {
                    $map_url = strip_request_item('map_url');
                }

                if ($request->has('description')) {
                    $description = strip_request_item_nl('description');
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
                        $msg .= error(sprintf(
                            __('Please enter needed angels for type %s.'),
                            $angeltype
                        ), true);
                    }
                }

                if ($valid) {
                    if (empty($room_id)) {
                        $room_id = Room_create($name, $map_url, $description);
                    } else {
                        Room_update($room_id, $name, $map_url, $description);
                    }

                    NeededAngelTypes_delete_by_room($room_id);
                    $needed_angeltype_info = [];
                    foreach ($angeltypes_count as $angeltype_id => $angeltype_count) {
                        $angeltype = AngelType($angeltype_id);
                        if (!empty($angeltype)) {
                            NeededAngelType_add(null, $angeltype_id, $room_id, $angeltype_count);
                            if ($angeltype_count > 0) {
                                $needed_angeltype_info[] = $angeltype['name'] . ': ' . $angeltype_count;
                            }
                        }
                    }

                    engelsystem_log(
                        'Set needed angeltypes of room ' . $name
                        . ' to: ' . join(', ', $needed_angeltype_info)
                    );
                    success(__('Room saved.'));
                    throw_redirect(page_link_to('admin_rooms'));
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
                    button(page_link_to('admin_rooms'), __('back'), 'back')
                ]),
                $msg,
                form([
                    div('row', [
                        div('col-md-6', [
                            form_text('name', __('Name'), $name, false, 35),
                            form_text('map_url', __('Map URL'), $map_url),
                            form_info('', __('The map url is used to display an iframe on the room page.')),
                            form_textarea('description', __('Description'), $description),
                            form_info('', __('Please use markdown for the description.')),
                        ]),
                        div('col-md-6', [
                            div('row', [
                                div('col-md-12', [
                                    form_info(__('Needed angels:'))
                                ]),
                                join($angeltypes_count_form)
                            ])
                        ])
                    ]),
                    form_submit('submit', __('Save'))
                ])
            ], true);
        } elseif ($request->input('show') == 'delete') {
            if ($request->hasPostData('ack')) {
                $room = Room::find($room_id);
                $shifts = Shifts_by_room($room);
                foreach ($shifts as $shift) {
                    $shift = Shift($shift['SID']);

                    UserWorkLog_from_shift($shift);
                    mail_shift_delete($shift);
                }

                Room_delete($room);

                success(sprintf(__('Room %s deleted.'), $name));
                throw_redirect(page_link_to('admin_rooms'));
            }

            return page_with_title(admin_rooms_title(), [
                buttons([
                    button(page_link_to('admin_rooms'), __('back'), 'back')
                ]),
                sprintf(__('Do you want to delete room %s?'), $name),
                form([
                    form_submit('ack', __('Delete'), 'delete btn-danger'),
                ], page_link_to('admin_rooms', ['show' => 'delete', 'id' => $room_id])),
            ], true);
        }
    }

    return page_with_title(admin_rooms_title(), [
        buttons([
            button(page_link_to('admin_rooms', ['show' => 'edit']), __('add'))
        ]),
        msg(),
        table([
            'name'      => __('Name'),
            'map_url'   => __('Map'),
            'actions'   => ''
        ], $rooms)
    ], true);
}
