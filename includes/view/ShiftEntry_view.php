<?php

/**
 * Sign off from an user from a shift with admin permissions, asking for ack.
 *
 * @param array $shiftEntry
 * @param array $shift
 * @param array $angeltype
 * @param array $signoff_user
 *
 * @return string HTML
 */
function ShiftEntry_delete_view_admin($shiftEntry, $shift, $angeltype, $signoff_user)
{
    return page_with_title(ShiftEntry_delete_title(), [
        info(sprintf(
            _('Do you want to sign off %s from shift %s from %s to %s as %s?'),
            User_Nick_render($signoff_user),
            $shift['name'],
            date('Y-m-d H:i', $shift['start']),
            date('Y-m-d H:i', $shift['end']),
            $angeltype['name']
        ), true),
        buttons([
            button(user_link($signoff_user), glyph('remove') . _('cancel')),
            button(shift_entry_delete_link($shiftEntry, [
                'continue' => 1
            ]), glyph('ok') . _('delete'), 'btn-danger')
        ])
    ]);
}

/**
 * Sign off from a shift, asking for ack.
 *
 * @param array $shiftEntry
 * @param array $shift
 * @param array $angeltype
 * @param array $signoff_user
 *
 * @return string HTML
 */
function ShiftEntry_delete_view($shiftEntry, $shift, $angeltype, $signoff_user)
{
    return page_with_title(ShiftEntry_delete_title(), [
        info(sprintf(
            _('Do you want to sign off from your shift %s from %s to %s as %s?'),
            $shift['name'],
            date('Y-m-d H:i', $shift['start']),
            date('Y-m-d H:i', $shift['end']),
            $angeltype['name']
        ), true),
        buttons([
            button(user_link($signoff_user), glyph('remove') . _('cancel')),
            button(shift_entry_delete_link($shiftEntry, [
                'continue' => 1
            ]), glyph('ok') . _('delete'), 'btn-danger')
        ])
    ]);
}

/**
 * Title for deleting a shift entry.
 */
function ShiftEntry_delete_title()
{
    return _('Shift sign off');
}

/**
 * Admin puts user into shift.
 *
 * @param array $shift
 * @param array $room
 * @param array $angeltype
 * @param array $angeltypes_select
 * @param array $signup_user
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
            info(_('Do you want to sign up the following user for this shift?'), true),
            form([
                form_select('angeltype_id', _('Angeltype'), $angeltypes_select, $angeltype['id']),
                form_select('user_id', _('User'), $users_select, $signup_user['UID']),
                form_submit('submit', glyph('ok') . _('Save'))
            ])
        ]);
}

/**
 * Supporter puts user into shift.
 *
 * @param array $shift
 * @param array $room
 * @param array $angeltype
 * @param array $signup_user
 * @param array $users_select
 * @return string
 */
function ShiftEntry_create_view_supporter($shift, $room, $angeltype, $signup_user, $users_select)
{
    return page_with_title(ShiftEntry_create_title() . ': ' . $shift['name']
        . ' <small class="moment-countdown" data-timestamp="' . $shift['start'] . '">%c</small>',
        [
            Shift_view_header($shift, $room),
            info(sprintf(_('Do you want to sign up the following user for this shift as %s?'),
                AngelType_name_render($angeltype)), true),
            form([
                form_select('user_id', _('User'), $users_select, $signup_user['UID']),
                form_submit('submit', glyph('ok') . _('Save'))
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
            info(sprintf(_('Do you want to sign up for this shift as %s?'), AngelType_name_render($angeltype)), true),
            form([
                form_textarea('comment', _('Comment (for your eyes only):'), $comment),
                form_submit('submit', glyph('ok') . _('Save'))
            ])
        ]);
}

/**
 * Title for creating a shift entry.
 */
function ShiftEntry_create_title()
{
    return _('Shift signup');
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
            form_checkbox('freeloaded', _('Freeloaded'), $freeloaded),
            form_textarea(
                'freeload_comment',
                _('Freeload comment (Only for shift coordination):'),
                $freeload_comment
            )
        ];
    }
    return page_with_title(_('Edit shift entry'), [
        msg(),
        form([
            form_info(_('Angel:'), $angel),
            form_info(_('Date, Duration:'), $date),
            form_info(_('Location:'), $location),
            form_info(_('Title:'), $title),
            form_info(_('Type:'), $type),
            form_textarea('comment', _('Comment (for your eyes only):'), $comment),
            join('', $freeload_form),
            form_submit('submit', _('Save'))
        ])
    ]);
}
