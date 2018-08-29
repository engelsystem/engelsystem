<?php

/**
 * @param array $shifttype
 * @return string
 */
function ShiftType_name_render($shifttype)
{
    global $privileges;
    if (in_array('shifttypes', $privileges)) {
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
        buttons([
            button(page_link_to('shifttypes'), glyph('remove') . __('cancel')),
            button(
                page_link_to(
                    'shifttypes',
                    ['action' => 'delete', 'shifttype_id' => $shifttype['id'], 'confirmed' => 1]
                ),
                glyph('ok') . __('delete'),
                'btn-danger'
            )
        ])
    ]);
}

/**
 * @param string   $name
 * @param int      $angeltype_id
 * @param array[]  $angeltypes
 * @param string   $description
 * @param int|bool $shifttype_id
 * @return string
 */
function ShiftType_edit_view($name, $angeltype_id, $angeltypes, $description, $shifttype_id)
{
    $angeltypes_select = [
        '' => __('All')
    ];
    foreach ($angeltypes as $angeltype) {
        $angeltypes_select[$angeltype['id']] = $angeltype['name'];
    }

    return page_with_title($shifttype_id ? __('Edit shifttype') : __('Create shifttype'), [
        msg(),
        buttons([
            button(page_link_to('shifttypes'), shifttypes_title(), 'back')
        ]),
        form([
            form_text('name', __('Name'), $name),
            form_select('angeltype_id', __('Angeltype'), $angeltypes_select, $angeltype_id),
            form_textarea('description', __('Description'), $description),
            form_info('', __('Please use markdown for the description.')),
            form_submit('submit', __('Save'))
        ])
    ]);
}

/**
 * @param array $shifttype
 * @param array $angeltype
 * @return string
 */
function ShiftType_view($shifttype, $angeltype)
{
    $parsedown = new Parsedown();
    $title = $shifttype['name'];
    if ($angeltype) {
        $title .= ' <small>' . sprintf(__('for team %s'), $angeltype['name']) . '</small>';
    }
    return page_with_title($title, [
        msg(),
        buttons([
            button(page_link_to('shifttypes'), shifttypes_title(), 'back'),
            $angeltype ? button(
                page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]),
                $angeltype['name']
            ) : '',
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
        $parsedown->parse($shifttype['description'])
    ]);
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
                'btn-xs'
            ),
            button(
                page_link_to('shifttypes', ['action' => 'delete', 'shifttype_id' => $shifttype['id']]),
                __('delete'),
                'btn-xs'
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
    ]);
}
