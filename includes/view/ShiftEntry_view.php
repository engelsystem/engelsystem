<?php

use Engelsystem\Models\User\User;

/**
 * Sign off from an user from a shift with admin permissions, asking for ack.
 *
 * @param array $shiftEntry
 * @param array $shift
 * @param array $angeltype
 * @param User  $signoff_user
 *
 * @return string HTML
 */
function ShiftEntry_delete_view_admin($shiftEntry, $shift, $angeltype, $signoff_user)
{
    return page_with_title(ShiftEntry_delete_title(), [
        info(sprintf(
            __('Do you want to sign off %s from shift %s from %s to %s as %s?'),
            User_Nick_render($signoff_user),
            $shift['name'],
            date('Y-m-d H:i', $shift['start']),
            date('Y-m-d H:i', $shift['end']),
            $angeltype['name']
        ), true),
        buttons([
            button(user_link($signoff_user->id), glyph('remove') . __('cancel')),
            button(shift_entry_delete_link($shiftEntry, [
                'continue' => 1
            ]), glyph('ok') . __('delete'), 'btn-danger')
        ])
    ]);
}

/**
 * Sign off from a shift, asking for ack.
 *
 * @param array $shiftEntry
 * @param array $shift
 * @param array $angeltype
 * @param int   $signoff_user_id
 *
 * @return string HTML
 */
function ShiftEntry_delete_view($shiftEntry, $shift, $angeltype, $signoff_user_id)
{
    return page_with_title(ShiftEntry_delete_title(), [
        info(sprintf(
            __('Do you want to sign off from your shift %s from %s to %s as %s?'),
            $shift['name'],
            date('Y-m-d H:i', $shift['start']),
            date('Y-m-d H:i', $shift['end']),
            $angeltype['name']
        ), true),
        buttons([
            button(user_link($signoff_user_id), glyph('remove') . __('cancel')),
            button(shift_entry_delete_link($shiftEntry, [
                'continue' => 1
            ]), glyph('ok') . __('delete'), 'btn-danger')
        ])
    ]);
}

/**
 * Title for deleting a shift entry.
 */
function ShiftEntry_delete_title()
{
    return __('Shift sign off');
}

/**
 * Admin puts user into shift.
 *
 * @param array $shift
 * @param array $room
 * @param array $angeltype
 * @param array $angeltypes_select
 * @param User  $signup_user
 * @param array $users_select
 * @return string
 */
function ShiftEntry_create_view_admin($shift, $room, $angeltype, $angeltypes_select, $signup_user, $users_select)
{
    return page_with_title(
        ShiftEntry_create_title() . ': ' . $shift['name']
        . ' <small class="moment-countdown" data-timestamp="' . $shift['start'] . '">%c</small>',
        [
            Shift_view_header($shift, $room),
            info(__('Do you want to sign up the following user for this shift?'), true),
            form([
                form_select('angeltype_id', __('Angeltype'), $angeltypes_select, $angeltype['id']),
                form_select('user_id', __('User'), $users_select, $signup_user->id),
                form_submit('submit', glyph('ok') . __('Save'))
            ])
        ]);
}

/**
 * Supporter puts user into shift.
 *
 * @param array $shift
 * @param array $room
 * @param array $angeltype
 * @param User  $signup_user
 * @param array $users_select
 * @return string
 */
function ShiftEntry_create_view_supporter($shift, $room, $angeltype, $signup_user, $users_select)
{
    return page_with_title(ShiftEntry_create_title() . ': ' . $shift['name']
        . ' <small class="moment-countdown" data-timestamp="' . $shift['start'] . '">%c</small>',
        [
            Shift_view_header($shift, $room),
            info(sprintf(__('Do you want to sign up the following user for this shift as %s?'),
                AngelType_name_render($angeltype)), true),
            form([
                form_select('user_id', __('User'), $users_select, $signup_user->id),
                form_submit('submit', glyph('ok') . __('Save'))
            ])
        ]);
}

/**
 * User joining a shift.
 *
 * @param array  $shift
 * @param array  $room
 * @param array  $angeltype
 * @param string $comment
 * @return string
 */
function ShiftEntry_create_view_user($shift, $room, $angeltype, $comment)
{
    return page_with_title(ShiftEntry_create_title() . ': ' . $shift['name']
        . ' <small class="moment-countdown" data-timestamp="' . $shift['start'] . '">%c</small>',
        [
            Shift_view_header($shift, $room),
            info(sprintf(__('Do you want to sign up for this shift as %s?'), AngelType_name_render($angeltype)), true),
            form([
                form_textarea('comment', __('Comment (for your eyes only):'), $comment),
                form_submit('submit', glyph('ok') . __('Save'))
            ])
        ]);
}

/**
 * Title for creating a shift entry.
 */
function ShiftEntry_create_title()
{
    return __('Shift signup');
}

/**
 * Display form for adding/editing a shift entry.
 *
 * @param string $angel
 * @param string $date
 * @param string $location
 * @param string $title
 * @param string $type
 * @param string $comment
 * @param bool   $freeloaded
 * @param string $freeload_comment
 * @param bool   $user_admin_shifts
 * @return string
 */
function ShiftEntry_edit_view(
    $angel,
    $date,
    $location,
    $title,
    $type,
    $comment,
    $freeloaded,
    $freeload_comment,
    $user_admin_shifts = false
) {
    $freeload_form = [];
    if ($user_admin_shifts) {
        $freeload_form = [
            form_checkbox('freeloaded', __('Freeloaded'), $freeloaded),
            form_textarea(
                'freeload_comment',
                __('Freeload comment (Only for shift coordination):'),
                $freeload_comment
            )
        ];
    }
    return page_with_title(__('Edit shift entry'), [
        msg(),
        form([
            form_info(__('Angel:'), $angel),
            form_info(__('Date, Duration:'), $date),
            form_info(__('Location:'), $location),
            form_info(__('Title:'), $title),
            form_info(__('Type:'), $type),
            form_textarea('comment', __('Comment (for your eyes only):'), $comment),
            join('', $freeload_form),
            form_submit('submit', __('Save'))
        ])
    ]);
}
