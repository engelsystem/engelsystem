<?php

/**
 * @param array $shifttype
 * @return string
 */
function shifttype_link($shifttype)
{
    return page_link_to('shifttypes', ['action' => 'view', 'shifttype_id' => $shifttype['id']]);
}

/**
 * Delete a shifttype.
 *
 * @return array
 */
function shifttype_delete_controller()
{
    $request = request();
    if (!$request->has('shifttype_id')) {
        redirect(page_link_to('shifttypes'));
    }

    $shifttype = ShiftType($request->input('shifttype_id'));

    if ($shifttype == null) {
        redirect(page_link_to('shifttypes'));
    }

    if ($request->has('confirmed')) {
        $result = ShiftType_delete($shifttype['id']);
        if (empty($result)) {
            engelsystem_error('Unable to delete shifttype.');
        }

        engelsystem_log('Deleted shifttype ' . $shifttype['name']);
        success(sprintf(_('Shifttype %s deleted.'), $shifttype['name']));
        redirect(page_link_to('shifttypes'));
    }

    return [
        sprintf(_('Delete shifttype %s'), $shifttype['name']),
        ShiftType_delete_view($shifttype)
    ];
}

/**
 * Edit or create shift type.
 *
 * @return array
 */
function shifttype_edit_controller()
{
    $shifttype_id = null;
    $name = '';
    $angeltype_id = null;
    $description = '';

    $angeltypes = AngelTypes();
    $request = request();

    if ($request->has('shifttype_id')) {
        $shifttype = ShiftType($request->input('shifttype_id'));
        if ($shifttype == null) {
            error(_('Shifttype not found.'));
            redirect(page_link_to('shifttypes'));
        }
        $shifttype_id = $shifttype['id'];
        $name = $shifttype['name'];
        $angeltype_id = $shifttype['angeltype_id'];
        $description = $shifttype['description'];
    }

    if ($request->has('submit')) {
        $valid = true;

        if ($request->has('name') && $request->input('name') != '') {
            $name = strip_request_item('name');
        } else {
            $valid = false;
            error(_('Please enter a name.'));
        }

        if ($request->has('angeltype_id') && preg_match('/^\d+$/', $request->input('angeltype_id'))) {
            $angeltype_id = $request->input('angeltype_id');
        } else {
            $angeltype_id = null;
        }

        if ($request->has('description')) {
            $description = strip_request_item_nl('description');
        }

        if ($valid) {
            if ($shifttype_id) {
                $result = ShiftType_update($shifttype_id, $name, $angeltype_id, $description);
                if ($result === false) {
                    engelsystem_error('Unable to update shifttype.');
                }
                engelsystem_log('Updated shifttype ' . $name);
                success(_('Updated shifttype.'));
            } else {
                $shifttype_id = ShiftType_create($name, $angeltype_id, $description);
                if ($shifttype_id === false) {
                    engelsystem_error('Unable to create shifttype.');
                }
                engelsystem_log('Created shifttype ' . $name);
                success(_('Created shifttype.'));
            }
            redirect(page_link_to('shifttypes', ['action' => 'view', 'shifttype_id' => $shifttype_id]));
        }
    }

    return [
        shifttypes_title(),
        ShiftType_edit_view($name, $angeltype_id, $angeltypes, $description, $shifttype_id)
    ];
}

/**
 * @return array
 */
function shifttype_controller()
{
    $request = request();
    if (!$request->has('shifttype_id')) {
        redirect(page_link_to('shifttypes'));
    }
    $shifttype = ShiftType($request->input('shifttype_id'));
    if ($shifttype == null) {
        redirect(page_link_to('shifttypes'));
    }

    $angeltype = null;
    if ($shifttype['angeltype_id'] != null) {
        $angeltype = AngelType($shifttype['angeltype_id']);
    }

    return [
        $shifttype['name'],
        ShiftType_view($shifttype, $angeltype)
    ];
}

/**
 * List all shift types.
 *
 * @return array
 */
function shifttypes_list_controller()
{
    $shifttypes = ShiftTypes();
    if ($shifttypes === false) {
        engelsystem_error('Unable to load shifttypes.');
    }

    return [
        shifttypes_title(),
        ShiftTypes_list_view($shifttypes)
    ];
}

/**
 * Text for shift type related links.
 *
 * @return string
 */
function shifttypes_title()
{
    return _('Shifttypes');
}

/**
 * Route shift type actions
 *
 * @return array
 */
function shifttypes_controller()
{
    $request = request();
    $action = 'list';
    if ($request->has('action')) {
        $action = $request->input('action');
    }

    switch ($action) {
        case 'view':
            return shifttype_controller();
        case 'edit':
            return shifttype_edit_controller();
        case 'delete':
            return shifttype_delete_controller();
        case 'list':
        default:
            return shifttypes_list_controller();
    }
}
