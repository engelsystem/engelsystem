<?php

use Engelsystem\Database\DB;

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
    $rooms_source = DB::select('SELECT * FROM `Room` ORDER BY `Name`');
    $rooms = [];
    $request = request();

    foreach ($rooms_source as $room) {
        $rooms[] = [
            'name'           => Room_name_render($room),
            'from_pentabarf' => $room['FromPentabarf'] == 'Y' ? '&#10003;' : '',
            'public'         => $room['show'] == 'Y' ? '&#10003;' : '',
            'actions'        => table_buttons([
                button(page_link_to('admin_rooms') . '&show=edit&id=' . $room['RID'], _('edit'), 'btn-xs'),
                button(page_link_to('admin_rooms') . '&show=delete&id=' . $room['RID'], _('delete'), 'btn-xs')
            ])
        ];
    }
    $room = null;

    if ($request->has('show')) {
        $msg = '';
        $name = '';
        $from_pentabarf = '';
        $public = 'Y';
        $number = '';
        $room_id = 0;

        $angeltypes_source = DB::select('SELECT `id`, `name` FROM `AngelTypes` ORDER BY `name`');
        $angeltypes = [];
        $angeltypes_count = [];
        foreach ($angeltypes_source as $angeltype) {
            $angeltypes[$angeltype['id']] = $angeltype['name'];
            $angeltypes_count[$angeltype['id']] = 0;
        }

        if (test_request_int('id')) {
            $room = Room($request->input('id'), false);
            if ($room == null) {
                redirect(page_link_to('admin_rooms'));
            }

            $room_id = $request->input('id');
            $name = $room['Name'];
            $from_pentabarf = $room['FromPentabarf'];
            $public = $room['show'];
            $number = $room['Number'];

            $needed_angeltypes = DB::select(
                'SELECT `angel_type_id`, `count` FROM `NeededAngelTypes` WHERE `room_id`=?',
                [$room_id]
            );
            foreach ($needed_angeltypes as $needed_angeltype) {
                $angeltypes_count[$needed_angeltype['angel_type_id']] = $needed_angeltype['count'];
            }
        }

        if ($request->input('show') == 'edit') {
            if ($request->has('submit')) {
                $valid = true;

                if ($request->has('name') && strlen(strip_request_item('name')) > 0) {
                    $name = strip_request_item('name');
                    if (
                        isset($room)
                        && count(DB::select(
                            'SELECT RID FROM `Room` WHERE `Name`=? AND NOT `RID`=?',
                            [$name, $room_id]
                        )) > 0
                    ) {
                        $valid = false;
                        $msg .= error(_('This name is already in use.'), true);
                    }
                } else {
                    $valid = false;
                    $msg .= error(_('Please enter a name.'), true);
                }

                $from_pentabarf = '';
                if ($request->has('from_pentabarf')) {
                    $from_pentabarf = 'Y';
                }

                $public = '';
                if ($request->has('public')) {
                    $public = 'Y';
                }

                if ($request->has('number')) {
                    $number = strip_request_item('number');
                } else {
                    $valid = false;
                }

                foreach ($angeltypes as $angeltype_id => $angeltype) {
                    if (
                        $request->has('angeltype_count_' . $angeltype_id)
                        && preg_match('/^\d{1,4}$/', $request->input('angeltype_count_' . $angeltype_id))
                    ) {
                        $angeltypes_count[$angeltype_id] = $request->input('angeltype_count_' . $angeltype_id);
                    } else {
                        $valid = false;
                        $msg .= error(sprintf(_('Please enter needed angels for type %s.'), $angeltype), true);
                    }
                }

                if ($valid) {
                    if (!empty($room_id)) {
                        DB::update('
                            UPDATE `Room`
                            SET
                                `Name`=?,
                                `FromPentabarf`=?,
                                `show`=?,
                                `Number`=?
                            WHERE `RID`=?
                            LIMIT 1
                        ', [
                            $name,
                            $from_pentabarf,
                            $public,
                            $number,
                            $room_id,
                        ]);
                        engelsystem_log(
                            'Room updated: ' . $name
                            . ', pentabarf import: ' . $from_pentabarf
                            . ', public: ' . $public
                            . ', number: ' . $number
                        );
                    } else {
                        $room_id = Room_create($name, $from_pentabarf, $public, $number);

                        engelsystem_log(
                            'Room created: ' . $name
                            . ', pentabarf import: '
                            . $from_pentabarf
                            . ', public: ' . $public
                            . ', number: ' . $number
                        );
                    }

                    NeededAngelTypes_delete_by_room($room_id);
                    $needed_angeltype_info = [];
                    foreach ($angeltypes_count as $angeltype_id => $angeltype_count) {
                        $angeltype = AngelType($angeltype_id);
                        if ($angeltype != null) {
                            NeededAngelType_add(null, $angeltype_id, $room_id, $angeltype_count);
                            $needed_angeltype_info[] = $angeltype['name'] . ': ' . $angeltype_count;
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
                            form_checkbox('from_pentabarf', _('Frab import'), $from_pentabarf),
                            form_checkbox('public', _('Public'), $public),
                            form_text('number', _('Room number'), $number)
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
                        page_link_to('admin_rooms') . '&show=delete&id=' . $room_id . '&ack',
                        _('Delete'),
                        'delete btn-danger'
                    )
                ])
            ]);
        }
    }

    return page_with_title(admin_rooms_title(), [
        buttons([
            button(page_link_to('admin_rooms') . '&show=edit', _('add'))
        ]),
        msg(),
        table([
            'name'           => _('Name'),
            'from_pentabarf' => _('Frab import'),
            'public'         => _('Public'),
            'actions'        => ''
        ], $rooms)
    ]);
}
