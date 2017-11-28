<?php

use Engelsystem\ShiftSignupState;

/**
 * @param array $shift
 * @return string
 */
function Shift_editor_info_render($shift)
{
    $info = [];
    if ($shift['created_by_user_id'] != null) {
        $info[] = sprintf(
            glyph('plus') . _('created at %s by %s'),
            date('Y-m-d H:i', $shift['created_at_timestamp']),
            User_Nick_render(User($shift['created_by_user_id']))
        );
    }
    if ($shift['edited_by_user_id'] != null) {
        $info[] = sprintf(
            glyph('pencil') . _('edited at %s by %s'),
            date('Y-m-d H:i', $shift['edited_at_timestamp']),
            User_Nick_render(User($shift['edited_by_user_id']))
        );
    }
    return join('<br />', $info);
}

/**
 * @param array $shift
 * @param array $angeltype
 * @param array $user_angeltype
 * @return string
 */
function Shift_signup_button_render($shift, $angeltype, $user_angeltype = null)
{
    global $user;

    if ($user_angeltype == null) {
        $user_angeltype = UserAngelType_by_User_and_AngelType($user, $angeltype);
    }

    if ($angeltype['shift_signup_state']->isSignupAllowed()) {
        return button(
            page_link_to('user_shifts', ['shift_id' => $shift['SID'], 'type_id' => $angeltype['id']]),
            _('Sign up')
        );
    } elseif ($user_angeltype == null) {
        return button(
            page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]),
            sprintf(_('Become %s'),
                $angeltype['name'])
        );
    }
    return '';
}

/**
 * @param array            $shift
 * @param array            $shifttype
 * @param array            $room
 * @param array[]          $angeltypes_source
 * @param ShiftSignupState $shift_signup_state
 * @return string
 */
function Shift_view($shift, $shifttype, $room, $angeltypes_source, ShiftSignupState $shift_signup_state)
{
    global $privileges;

    $shift_admin = in_array('admin_shifts', $privileges);
    $user_shift_admin = in_array('user_shifts_admin', $privileges);
    $admin_rooms = in_array('admin_rooms', $privileges);
    $admin_shifttypes = in_array('shifttypes', $privileges);

    $parsedown = new Parsedown();

    $angeltypes = [];
    foreach ($angeltypes_source as $angeltype) {
        $angeltypes[$angeltype['id']] = $angeltype;
    }

    $needed_angels = '';
    foreach ($shift['NeedAngels'] as $needed_angeltype) {
        $needed_angels .= Shift_view_render_needed_angeltype($needed_angeltype, $angeltypes, $shift, $user_shift_admin);
    }

    return page_with_title(
        $shift['name'] . ' <small class="moment-countdown" data-timestamp="' . $shift['start'] . '">%c</small>',
        [
            msg(),
            $shift_signup_state->getState() == ShiftSignupState::COLLIDES
                ? info(_('This shift collides with one of your shifts.'), true)
                : '',
            $shift_signup_state->getState() == ShiftSignupState::SIGNED_UP
                ? info(_('You are signed up for this shift.'), true)
                : '',
            ($shift_admin || $admin_shifttypes || $admin_rooms) ? buttons([
                $shift_admin ? button(shift_edit_link($shift), glyph('pencil') . _('edit')) : '',
                $shift_admin ? button(shift_delete_link($shift), glyph('trash') . _('delete')) : '',
                $admin_shifttypes ? button(shifttype_link($shifttype), $shifttype['name']) : '',
                $admin_rooms ? button(room_link($room), glyph('map-marker') . $room['Name']) : ''
            ]) : '',
            div('row', [
                div('col-sm-3 col-xs-6', [
                    '<h4>' . _('Title') . '</h4>',
                    '<p class="lead">' . ($shift['URL'] != '' ? '<a href="' . $shift['URL'] . '">' . $shift['title'] . '</a>' : $shift['title']) . '</p>'
                ]),
                div('col-sm-3 col-xs-6', [
                    '<h4>' . _('Start') . '</h4>',
                    '<p class="lead' . (time() >= $shift['start'] ? ' text-success' : '') . '">',
                    glyph('calendar') . date(_('Y-m-d'), $shift['start']),
                    '<br />',
                    glyph('time') . date('H:i', $shift['start']),
                    '</p>'
                ]),
                div('col-sm-3 col-xs-6', [
                    '<h4>' . _('End') . '</h4>',
                    '<p class="lead' . (time() >= $shift['end'] ? ' text-success' : '') . '">',
                    glyph('calendar') . date(_('Y-m-d'), $shift['end']),
                    '<br />',
                    glyph('time') . date('H:i', $shift['end']),
                    '</p>'
                ]),
                div('col-sm-3 col-xs-6', [
                    '<h4>' . _('Location') . '</h4>',
                    '<p class="lead">' . Room_name_render($room) . '</p>'
                ])
            ]),
            div('row', [
                div('col-sm-6', [
                    '<h2>' . _('Needed angels') . '</h2>',
                    '<div class="list-group">' . $needed_angels . '</div>'
                ]),
                div('col-sm-6', [
                    '<h2>' . _('Description') . '</h2>',
                    $parsedown->parse($shifttype['description'])
                ])
            ]),
            $shift_admin ? Shift_editor_info_render($shift) : ''
        ]
    );
}

/**
 * @param array   $needed_angeltype
 * @param array   $angeltypes
 * @param array[] $shift
 * @param bool    $user_shift_admin
 * @return string
 */
function Shift_view_render_needed_angeltype($needed_angeltype, $angeltypes, $shift, $user_shift_admin)
{
    global $user;

    $angeltype = $angeltypes[$needed_angeltype['TID']];
    $angeltype_supporter = User_is_AngelType_supporter($user, $angeltype);

    $needed_angels = '';

    $class = 'progress-bar-warning';
    if ($needed_angeltype['taken'] == 0) {
        $class = 'progress-bar-danger';
    }
    if ($needed_angeltype['taken'] >= $needed_angeltype['count']) {
        $class = 'progress-bar-success';
    }
    $needed_angels .= '<div class="list-group-item">';

    $needed_angels .= '<div class="pull-right">' . Shift_signup_button_render($shift, $angeltype) . '</div>';

    $needed_angels .= '<h3>' . AngelType_name_render($angeltype) . '</h3>';
    $bar_max = max($needed_angeltype['count'] * 10, $needed_angeltype['taken'] * 10, 10);
    $bar_value = max(1, $needed_angeltype['taken'] * 10);
    $needed_angels .= progress_bar(
        0,
        $bar_max,
        $bar_value,
        $class,
        $needed_angeltype['taken'] . ' / ' . $needed_angeltype['count']
    );

    $angels = [];
    foreach ($shift['ShiftEntry'] as $shift_entry) {
        if ($shift_entry['TID'] == $needed_angeltype['TID']) {
            $angels[] = Shift_view_render_shift_entry($shift_entry, $user_shift_admin, $angeltype_supporter);
        }
    }

    $needed_angels .= join(', ', $angels);
    $needed_angels .= '</div>';

    return $needed_angels;
}

/**
 * @param array $shift_entry
 * @param bool  $user_shift_admin
 * @param bool  $angeltype_supporter
 * @return string
 */
function Shift_view_render_shift_entry($shift_entry, $user_shift_admin, $angeltype_supporter)
{
    $entry = User_Nick_render(User($shift_entry['UID']));
    if ($shift_entry['freeloaded']) {
        $entry = '<del>' . $entry . '</del>';
    }
    if ($user_shift_admin || $angeltype_supporter) {
        $entry .= ' <div class="btn-group">';
        if ($user_shift_admin) {
            $entry .= button_glyph(
                page_link_to('user_myshifts', ['edit' => $shift_entry['id'], 'id' => $shift_entry['UID']]),
                'pencil',
                'btn-xs'
            );
        }
        $entry .= button_glyph(page_link_to('user_shifts', ['entry_id' => $shift_entry['id']]), 'trash', 'btn-xs');
        $entry .= '</div>';
    }
    return $entry;
}

/**
 * Calc shift length in format 12:23h.
 *
 * @param array $shift
 * @return string
 */
function shift_length($shift)
{
    $length = floor(($shift['end'] - $shift['start']) / (60 * 60)) . ':';
    $length .= str_pad((($shift['end'] - $shift['start']) % (60 * 60)) / 60, 2, '0', STR_PAD_LEFT) . 'h';
    return $length;
}
