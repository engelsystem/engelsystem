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
function ShiftEntry_delete_view_admin($shiftEntry, $shift, $angeltype, $signoff_user) {
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
            button(ShiftEntry_delete_link($shiftEntry, ['continue' => 1]), glyph('ok') . _('delete'), 'btn-danger')
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
function ShiftEntry_delete_view($shiftEntry, $shift, $angeltype, $signoff_user) {
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
            button(ShiftEntry_delete_link($shiftEntry, ['continue' => 1]), glyph('ok') . _('delete'), 'btn-danger')
        ])
    ]);
}

/**
 * Link to delete a shift entry.
 * @param array $shiftEntry
 * 
 * @return string URL
 */
function ShiftEntry_delete_link($shiftEntry, $params = []) {
    $params = array_merge(['entry_id' => $shiftEntry['id']], $params);
    return page_link_to('user_shifts', $params);
}

/**
 * Title for deleting a shift entry.
 */
function ShiftEntry_delete_title() {
    return _('Shift sign off');
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
            form_textarea('freeload_comment', _('Freeload comment (Only for shift coordination):'), $freeload_comment)
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
