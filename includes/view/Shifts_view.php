<?php

use Engelsystem\Models\User\User;
use Engelsystem\ShiftSignupState;

/**
 * Renders the basic shift view header.
 *
 * @param array $shift
 * @param array $room
 * @return string HTML
 */
function Shift_view_header($shift, $room)
{
    return div('row', [
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('Title') . '</h4>',
            '<p class="lead">'
            . ($shift['URL'] != ''
                ? '<a href="' . $shift['URL'] . '">' . $shift['title'] . '</a>'
                : $shift['title'])
            . '</p>'
        ]),
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('Start') . '</h4>',
            '<p class="lead' . (time() >= $shift['start'] ? ' text-success' : '') . '">',
            glyph('calendar') . date(__('Y-m-d'), $shift['start']),
            '<br />',
            glyph('time') . date('H:i', $shift['start']),
            '</p>'
        ]),
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('End') . '</h4>',
            '<p class="lead' . (time() >= $shift['end'] ? ' text-success' : '') . '">',
            glyph('calendar') . date(__('Y-m-d'), $shift['end']),
            '<br />',
            glyph('time') . date('H:i', $shift['end']),
            '</p>'
        ]),
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('Location') . '</h4>',
            '<p class="lead">' . Room_name_render($room) . '</p>'
        ])
    ]);
}

/**
 * @param array $shift
 * @return string
 */
function Shift_editor_info_render($shift)
{
    $info = [];
    if (!empty($shift['created_by_user_id'])) {
        $info[] = sprintf(
            glyph('plus') . __('created at %s by %s'),
            date('Y-m-d H:i', $shift['created_at_timestamp']),
            User_Nick_render(User::find($shift['created_by_user_id']))
        );
    }
    if (!empty($shift['edited_by_user_id'])) {
        $info[] = sprintf(
            glyph('pencil') . __('edited at %s by %s'),
            date('Y-m-d H:i', $shift['edited_at_timestamp']),
            User_Nick_render(User::find($shift['edited_by_user_id']))
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
    if (empty($user_angeltype)) {
        $user_angeltype = UserAngelType_by_User_and_AngelType(auth()->user()->id, $angeltype);
    }

    if ($angeltype['shift_signup_state']->isSignupAllowed()) {
        return button(shift_entry_create_link($shift, $angeltype), __('Sign up'));
    } elseif (empty($user_angeltype)) {
        return button(
            page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]),
            sprintf(__('Become %s'),
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
    $shift_admin = auth()->can('admin_shifts');
    $user_shift_admin = auth()->can('user_shifts_admin');
    $admin_rooms = auth()->can('admin_rooms');
    $admin_shifttypes = auth()->can('shifttypes');

    $parsedown = new Parsedown();

    $angeltypes = [];
    foreach ($angeltypes_source as $angeltype) {
        $angeltypes[$angeltype['id']] = $angeltype;
    }

    $needed_angels = '';
    foreach ($shift['NeedAngels'] as $needed_angeltype) {
        $needed_angels .= Shift_view_render_needed_angeltype($needed_angeltype, $angeltypes, $shift, $user_shift_admin);
    }

    $content = [msg()];

    if ($shift_signup_state->getState() == ShiftSignupState::COLLIDES) {
        $content[] = info(__('This shift collides with one of your shifts.'), true);
    }

    if ($shift_signup_state->getState() == ShiftSignupState::SIGNED_UP) {
        $content[] = info(__('You are signed up for this shift.'), true);
    }

    if (config('signup_advance_hours') && $shift['start'] > time() + config('signup_advance_hours') * 3600) {
        $content[] = info(sprintf(
            _('This shift is in the far future and becomes available for signup at %s.'),
            date(_('Y-m-d') . ' H:i', $shift['start'] - config('signup_advance_hours') * 3600)
        ), true);
    }

    $buttons = [];
    if ($shift_admin || $admin_shifttypes || $admin_rooms) {
        $buttons = [
            $shift_admin ? button(shift_edit_link($shift), glyph('pencil') . __('edit')) : '',
            $shift_admin ? button(shift_delete_link($shift), glyph('trash') . __('delete')) : '',
            $admin_shifttypes ? button(shifttype_link($shifttype), $shifttype['name']) : '',
            $admin_rooms ? button(room_link($room), glyph('map-marker') . $room['Name']) : '',
        ];
    }
    $buttons[] = button(user_link(auth()->user()->id), '<span class="icon-icon_angel"></span> ' . __('My shifts'));
    $content[] = buttons($buttons);

    $content[] = Shift_view_header($shift, $room);
    $content[] = div('row', [
        div('col-sm-6', [
            '<h2>' . __('Needed angels') . '</h2>',
            '<div class="list-group">' . $needed_angels . '</div>'
        ]),
        div('col-sm-6', [
            '<h2>' . __('Description') . '</h2>',
            $parsedown->parse($shifttype['description'])
        ])
    ]);

    if ($shift_admin) {
        $content[] = Shift_editor_info_render($shift);
    }

    return page_with_title(
        $shift['name'] . ' <small class="moment-countdown" data-timestamp="' . $shift['start'] . '">%c</small>',
        $content
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
    $angeltype = $angeltypes[$needed_angeltype['TID']];
    $angeltype_supporter = User_is_AngelType_supporter(auth()->user(), $angeltype);

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
    $entry = User_Nick_render(User::find($shift_entry['UID']));
    if ($shift_entry['freeloaded']) {
        $entry = '<del>' . $entry . '</del>';
    }
    $isUser = $shift_entry['UID'] == auth()->user()->id;
    if ($user_shift_admin || $angeltype_supporter || $isUser) {
        $entry .= ' <div class="btn-group">';
        if ($user_shift_admin || $isUser) {
            $entry .= button_glyph(
                page_link_to('user_myshifts', ['edit' => $shift_entry['id'], 'id' => $shift_entry['UID']]),
                'pencil',
                'btn-xs'
            );
        }
        $entry .= button_glyph(shift_entry_delete_link($shift_entry), 'trash', 'btn-xs');
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
    $length .= str_pad(
            (($shift['end'] - $shift['start']) % (60 * 60)) / 60,
            2,
            '0',
            STR_PAD_LEFT
        ) . 'h';
    return $length;
}
