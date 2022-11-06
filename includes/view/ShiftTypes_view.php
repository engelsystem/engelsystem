<?php

/**
 * @param array $shifttype
 * @return string
 */
function ShiftType_name_render($shifttype)
{
    if (auth()->can('shifttypes')) {
        return '<a href="' . shifttype_link($shifttype) . '">' . $shifttype['name'] . '</a>';
    }
    return $shifttype['name'];
}

/**
 * @param array $shifttype
 * @return string
 */
function ShiftType_delete_view($shifttype)
{
    return page_with_title(sprintf(__('Delete shifttype %s'), $shifttype['name']), [
        info(sprintf(__('Do you want to delete shifttype %s?'), $shifttype['name']), true),
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
 * @param string   $name
 * @param string   $description
 * @param int|bool $shifttype_id
 * @return string
 */
function ShiftType_edit_view($name, $description, $shifttype_id)
{
    return page_with_title($shifttype_id ? __('Edit shifttype') : __('Create shifttype'), [
        msg(),
        buttons([
            button(page_link_to('shifttypes'), shifttypes_title(), 'back')
        ]),
        form([
            form_text('name', __('Name'), $name),
            form_textarea('description', __('Description'), $description),
            form_info('', __('Please use markdown for the description.')),
            form_submit('submit', __('Save'))
        ])
    ], true);
}

/**
 * @param array $shifttype
 * @return string
 */
function ShiftType_view($shifttype)
{
    $parsedown = new Parsedown();
    $title = $shifttype['name'];
    return page_with_title($title, [
        msg(),
        buttons([
            button(page_link_to('shifttypes'), shifttypes_title(), 'back'),
            button(
                page_link_to('shifttypes', ['action' => 'edit', 'shifttype_id' => $shifttype['id']]),
                __('edit'),
                'edit'
            ),
            button(
                page_link_to('shifttypes', ['action' => 'delete', 'shifttype_id' => $shifttype['id']]),
                __('delete'),
                'delete'
            )
        ]),
        heading(__('Description'), 2),
        $parsedown->parse((string)$shifttype['description'])
    ], true);
}

/**
 * @param array[] $shifttypes
 * @return string
 */
function ShiftTypes_list_view($shifttypes)
{
    foreach ($shifttypes as &$shifttype) {
        $shifttype['name'] = '<a href="'
            . page_link_to('shifttypes', ['action' => 'view', 'shifttype_id' => $shifttype['id']])
            . '">'
            . $shifttype['name']
            . '</a>';
        $shifttype['actions'] = table_buttons([
            button(
                page_link_to(
                    'shifttypes',
                    ['action' => 'edit', 'shifttype_id' => $shifttype['id']]
                ),
                __('edit'),
                'btn-sm'
            ),
            button(
                page_link_to('shifttypes', ['action' => 'delete', 'shifttype_id' => $shifttype['id']]),
                __('delete'),
                'btn-sm'
            )
        ]);
    }

    return page_with_title(shifttypes_title(), [
        msg(),
        buttons([
            button(page_link_to('shifttypes', ['action' => 'edit']), __('New shifttype'), 'add')
        ]),
        table([
            'name'    => __('Name'),
            'actions' => ''
        ], $shifttypes)
    ], true);
}
