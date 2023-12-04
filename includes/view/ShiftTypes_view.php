<?php

use Engelsystem\Models\Shifts\ShiftType;
use Illuminate\Support\Collection;

/**
 * @param ShiftType $shifttype
 * @return string
 */
function ShiftType_name_render(ShiftType $shifttype)
{
    if (auth()->can('shifttypes')) {
        return '<a href="' . shifttype_link($shifttype) . '">' . htmlspecialchars($shifttype->name) . '</a>';
    }
    return $shifttype->name;
}

/**
 * @param ShiftType $shifttype
 * @return string
 */
function ShiftType_delete_view(ShiftType $shifttype)
{
    return page_with_title(sprintf(__('Delete shifttype %s'), htmlspecialchars($shifttype->name)), [
        info(sprintf(__('Do you want to delete shifttype %s?'), $shifttype->name), true),
        form([
            buttons([
                button(page_link_to('shifttypes'), icon('x-lg') . __('cancel')),
                form_submit(
                    'delete',
                    icon('trash') . __('delete'),
                    'btn-danger',
                    false
                ),
            ]),
        ]),
    ], true);
}

/**
 * @param string $name
 * @param string $description
 * @param int    $shifttype_id
 * @return string
 */
function ShiftType_edit_view($name, $description, $shifttype_id)
{
    return page_with_title($shifttype_id ? __('Edit shifttype') : __('Create shifttype'), [
        msg(),
        buttons([
            button(page_link_to('shifttypes'), shifttypes_title(), 'back'),
        ]),
        form([
            form_text('name', __('Name'), $name),
            form_textarea('description', __('Description'), $description),
            form_info('', __('Please use markdown for the description.')),
            form_submit('submit', __('Save')),
        ]),
    ], true);
}

/**
 * @param ShiftType $shifttype
 * @return string
 */
function ShiftType_view(ShiftType $shifttype)
{
    $parsedown = new Parsedown();
    $title = $shifttype->name;
    return page_with_title(htmlspecialchars($title), [
        msg(),
        buttons([
            button(page_link_to('shifttypes'), shifttypes_title(), 'back'),
            button(
                page_link_to('shifttypes', ['action' => 'edit', 'shifttype_id' => $shifttype->id]),
                icon('pencil') . __('edit')
            ),
            button(
                page_link_to('shifttypes', ['action' => 'delete', 'shifttype_id' => $shifttype->id]),
                icon('trash') . __('delete'),
            ),
        ]),
        heading(__('Description'), 2),
        $parsedown->parse(htmlspecialchars($shifttype->description)),
    ], true);
}

/**
 * @param ShiftType[]|array[]|Collection $shifttypes
 * @return string
 */
function ShiftTypes_list_view($shifttypes)
{
    foreach ($shifttypes as $shifttype) {
        $shifttype->name = '<a href="'
            . page_link_to('shifttypes', ['action' => 'view', 'shifttype_id' => $shifttype->id])
            . '">'
            . htmlspecialchars($shifttype->name)
            . '</a>';
        $shifttype->actions = table_buttons([
            button(
                page_link_to(
                    'shifttypes',
                    ['action' => 'edit', 'shifttype_id' => $shifttype->id]
                ),
                icon('pencil') . __('edit'),
                'btn-sm'
            ),
            button(
                page_link_to('shifttypes', ['action' => 'delete', 'shifttype_id' => $shifttype->id]),
                icon('trash') . __('delete'),
                'btn-sm'
            ),
        ]);
    }

    return page_with_title(shifttypes_title(), [
        msg(),
        buttons([
            button(page_link_to('shifttypes', ['action' => 'edit']), __('New shifttype'), 'add'),
        ]),
        table([
            'name'    => __('Name'),
            'actions' => '',
        ], $shifttypes),
    ], true);
}
