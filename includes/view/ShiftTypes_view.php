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
        return '<a href="' . shifttype_link($shifttype) . '">' . $shifttype->name . '</a>';
    }
    return $shifttype->name;
}

/**
 * @param ShiftType $shifttype
 * @return string
 */
function ShiftType_delete_view(ShiftType $shifttype)
{
    return page_with_title(sprintf(__('Delete shifttype %s'), $shifttype->name), [
        info(sprintf(__('Do you want to delete shifttype %s?'), $shifttype->name), true),
        form([
            buttons([
                button(url('/shifttypes'), icon('x-lg') . __('form.cancel')),
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
    return page_with_title(
        $shifttype_id
        ? (button(
            url('/shifttypes', ['action' => 'view', 'shifttype_id' => $shifttype_id]),
            icon('chevron-left'),
            'btn-sm'
        ) . ' ' . __('Edit shifttype'))
        : (button(url('/shifttypes'), icon('chevron-left'), 'btn-sm') . ' ' . __('Create shifttype')),
        [
            msg(),
            buttons([
                button(url('/shifttypes'), shifttypes_title(), 'back'),
            ]),
            form([
                form_text('name', __('general.name'), $name),
                form_textarea('description', __('general.description'), $description),
                form_info('', __('Please use markdown for the description.')),
                form_submit('submit', __('form.save')),
            ]),
        ],
        true
    );
}

/**
 * @param ShiftType $shifttype
 * @return string
 */
function ShiftType_view(ShiftType $shifttype)
{
    $parsedown = new Parsedown();
    $title = $shifttype->name;
    $link = button(url('/shifttypes'), icon('chevron-left'), 'btn-sm');
    return page_with_title($link . ' ' . $title, [
        msg(),
        buttons([
            button(
                url('/shifttypes', ['action' => 'edit', 'shifttype_id' => $shifttype->id]),
                icon('pencil') . __('edit')
            ),
            button(
                url('/shifttypes', ['action' => 'delete', 'shifttype_id' => $shifttype->id]),
                icon('trash') . __('delete'),
            ),
        ]),
        heading(__('general.description'), 2),
        $parsedown->parse($shifttype->description),
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
            . url('/shifttypes', ['action' => 'view', 'shifttype_id' => $shifttype->id])
            . '">'
            . $shifttype->name
            . '</a>';
        $shifttype->actions = table_buttons([
            button(
                url(
                    '/shifttypes',
                    ['action' => 'edit', 'shifttype_id' => $shifttype->id]
                ),
                icon('pencil') . __('edit'),
                'btn-sm'
            ),
            button(
                url('/shifttypes', ['action' => 'delete', 'shifttype_id' => $shifttype->id]),
                icon('trash') . __('delete'),
                'btn-sm'
            ),
        ]);
    }

    $link = button(url('/shifttypes', ['action' => 'edit']), icon('plus-lg'), 'add');
    return page_with_title(
        shifttypes_title() . ' ' . $link,
        [
            msg(),

            table([
                'name'    => __('general.name'),
                'actions' => '',
            ], $shifttypes),
        ],
        true,
    );
}
