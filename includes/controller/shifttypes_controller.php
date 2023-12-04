<?php

use Engelsystem\Models\Shifts\ShiftType;

/**
 * @param ShiftType $shifttype
 * @return string
 */
function shifttype_link(ShiftType $shifttype)
{
    return page_link_to('shifttypes', ['action' => 'view', 'shifttype_id' => $shifttype->id]);
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
        throw_redirect(page_link_to('shifttypes'));
    }

    $shifttype = ShiftType::findOrFail($request->input('shifttype_id'));
    if ($request->hasPostData('delete')) {
        engelsystem_log('Deleted shifttype ' . $shifttype->name);
        success(sprintf(__('Shifttype %s deleted.'), $shifttype->name));

        $shifttype->delete();
        throw_redirect(page_link_to('shifttypes'));
    }

    return [
        sprintf(__('Delete shifttype %s'), htmlspecialchars($shifttype->name)),
        ShiftType_delete_view($shifttype),
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
    $description = '';

    $request = request();

    if ($request->has('shifttype_id')) {
        $shifttype = ShiftType::findOrFail($request->input('shifttype_id'));
        $shifttype_id = $shifttype->id;
        $name = $shifttype->name;
        $description = $shifttype->description;
    }

    if ($request->hasPostData('submit')) {
        $valid = true;

        if ($request->has('name') && $request->input('name') != '') {
            $name = strip_request_item('name');
        } else {
            $valid = false;
            error(__('Please enter a name.'));
        }

        if ($request->has('description')) {
            $description = strip_request_item_nl('description');
        }

        if ($valid) {
            $shiftType = ShiftType::findOrNew($shifttype_id);
            $shiftType->name = $name;
            $shiftType->description = $description;
            $shiftType->save();

            if ($shifttype_id) {
                engelsystem_log('Updated shifttype ' . $name);
                success(__('Updated shifttype.'));
            } else {
                $shifttype_id = $shiftType->id;

                engelsystem_log('Created shifttype ' . $name);
                success(__('Created shifttype.'));
            }

            throw_redirect(page_link_to('shifttypes', ['action' => 'view', 'shifttype_id' => $shifttype_id]));
        }
    }

    return [
        shifttypes_title(),
        ShiftType_edit_view($name, $description, $shifttype_id),
    ];
}

/**
 * @return array
 */
function shifttype_controller()
{
    $request = request();
    if (!$request->has('shifttype_id')) {
        throw_redirect(page_link_to('shifttypes'));
    }
    $shifttype = ShiftType::findOrFail($request->input('shifttype_id'));

    return [
        htmlspecialchars($shifttype->name),
        ShiftType_view($shifttype),
    ];
}

/**
 * List all shift types.
 *
 * @return array
 */
function shifttypes_list_controller()
{
    $shifttypes = ShiftType::all()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);

    return [
        shifttypes_title(),
        ShiftTypes_list_view($shifttypes),
    ];
}

/**
 * Text for shift type related links.
 *
 * @return string
 */
function shifttypes_title()
{
    return __('Shifttypes');
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

    return match ($action) {
        'view'   => shifttype_controller(),
        'edit'   => shifttype_edit_controller(),
        'delete' => shifttype_delete_controller(),
        'list'   => shifttypes_list_controller(),
        default  => shifttypes_list_controller(),
    };
}
