<?php

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\Shifts\ShiftSignupStatus;
use Engelsystem\Models\UserAngelType;
use Engelsystem\ShiftSignupState;
use Illuminate\Support\Collection;

/**
 * Renders the basic shift view header.
 *
 * @param Shift $shift
 * @param Room  $room
 * @return string HTML
 */
function Shift_view_header(Shift $shift, Room $room)
{
    return div('row', [
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('Title') . '</h4>',
            '<p class="lead">'
            . ($shift->url != ''
                ? '<a href="' . htmlspecialchars($shift->url) . '">' . htmlspecialchars($shift->title) . '</a>'
                : htmlspecialchars($shift->title))
            . '</p>',
        ]),
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('Start') . '</h4>',
            '<p class="lead' . (time() >= $shift->start->timestamp ? ' text-success' : '') . '">',
            icon('calendar-event') . $shift->start->format(__('Y-m-d')),
            '<br />',
            icon('clock') . $shift->start->format('H:i'),
            '</p>',
        ]),
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('End') . '</h4>',
            '<p class="lead' . (time() >= $shift->end->timestamp ? ' text-success' : '') . '">',
            icon('calendar-event') . $shift->end->format(__('Y-m-d')),
            '<br />',
            icon('clock') . $shift->end->format('H:i'),
            '</p>',
        ]),
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('Location') . '</h4>',
            '<p class="lead">' . Room_name_render($room) . '</p>',
        ]),
    ]);
}

/**
 * @param Shift $shift
 * @return string
 */
function Shift_editor_info_render(Shift $shift)
{
    $info = [];
    if (!empty($shift->created_by)) {
        $info[] = sprintf(
            icon('plus-lg') . __('created at %s by %s'),
            $shift->created_at->format(__('Y-m-d H:i')),
            User_Nick_render($shift->createdBy)
        );
    }
    if (!empty($shift->updated_by)) {
        $info[] = sprintf(
            icon('pencil') . __('edited at %s by %s'),
            $shift->updated_at->format(__('Y-m-d H:i')),
            User_Nick_render($shift->updatedBy)
        );
    }
    return join('<br />', $info);
}

/**
 * @param Shift     $shift
 * @param AngelType $angeltype
 * @return string
 */
function Shift_signup_button_render(Shift $shift, AngelType $angeltype)
{
    /** @var UserAngelType|null $user_angeltype */
    $user_angeltype = UserAngelType::whereUserId(auth()->user()->id)
        ->where('angel_type_id', $angeltype->id)
        ->first();

    if (
        $angeltype->shift_signup_state?->isSignupAllowed()
        || auth()->user()->isAngelTypeSupporter($angeltype)
        || auth()->can('admin_user_angeltypes')
    ) {
        return button(shift_entry_create_link($shift, $angeltype), __('Sign up'));
    } elseif (empty($user_angeltype)) {
        return button(
            page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]),
            sprintf(
                __('Become %s'),
                htmlspecialchars($angeltype->name)
            )
        );
    }

    return '';
}

/**
 * @param Shift                  $shift
 * @param ShiftType              $shifttype
 * @param Room                   $room
 * @param AngelType[]|Collection $angeltypes_source
 * @param ShiftSignupState       $shift_signup_state
 * @return string
 */
function Shift_view(Shift $shift, ShiftType $shifttype, Room $room, $angeltypes_source, ShiftSignupState $shift_signup_state)
{
    $shift_admin = auth()->can('admin_shifts');
    $user_shift_admin = auth()->can('user_shifts_admin');
    $admin_rooms = auth()->can('admin_rooms');
    $admin_shifttypes = auth()->can('shifttypes');

    $parsedown = new Parsedown();

    $angeltypes = [];
    foreach ($angeltypes_source as $angeltype) {
        $angeltypes[$angeltype->id] = $angeltype;
    }

    $needed_angels = '';
    $neededAngels = new Collection($shift->neededAngels);
    foreach ($neededAngels as $needed_angeltype) {
        $needed_angels .= Shift_view_render_needed_angeltype($needed_angeltype, $angeltypes, $shift, $user_shift_admin);
    }

    $shiftEntry = $shift->shiftEntries;
    foreach ($shiftEntry->groupBy('angel_type_id') as $angelTypes) {
        /** @var Collection $angelTypes */
        $type = $angelTypes->first()['angel_type_id'];
        if (!$neededAngels->where('angel_type_id', $type)->first()) {
            $needed_angels .= Shift_view_render_needed_angeltype([
                'angel_type_id' => $type,
                'count'         => 0,
                'restricted'    => true,
                'taken'         => $angelTypes->count(),
            ], $angeltypes, $shift, $user_shift_admin);
        }
    }

    $content = [msg()];

    if ($shift_signup_state->getState() === ShiftSignupStatus::COLLIDES) {
        $content[] = info(__('This shift collides with one of your shifts.'), true);
    }

    if ($shift_signup_state->getState() === ShiftSignupStatus::SIGNED_UP) {
        $content[] = info(__('You are signed up for this shift.'), true);
    }

    if (config('signup_advance_hours') && $shift->start->timestamp > time() + config('signup_advance_hours') * 3600) {
        $content[] = info(sprintf(
            __('This shift is in the far future and becomes available for signup at %s.'),
            date(__('Y-m-d H:i'), $shift->start->timestamp - config('signup_advance_hours') * 3600)
        ), true);
    }

    $buttons = [];
    if ($shift_admin || $admin_shifttypes || $admin_rooms) {
        $buttons = [
            $shift_admin ? button(shift_edit_link($shift), icon('pencil') . __('edit')) : '',
            $shift_admin ? button(shift_delete_link($shift), icon('trash') . __('delete')) : '',
            $admin_shifttypes
                ? button(shifttype_link($shifttype), htmlspecialchars($shifttype->name))
                : '',
            $admin_rooms
                ? button(
                    room_link($room),
                    icon('pin-map-fill') . htmlspecialchars($room->name)
                )
                : '',
        ];
    }
    $buttons[] = button(user_link(auth()->user()->id), '<span class="icon-icon_angel"></span> ' . __('My shifts'));
    $content[] = buttons($buttons);

    $content[] = Shift_view_header($shift, $room);
    $content[] = div('row', [
        div('col-sm-6', [
            '<h2>' . __('Needed angels') . '</h2>',
            '<div class="list-group">' . $needed_angels . '</div>',
        ]),
        div('col-sm-6', [
            '<h2>' . __('Description') . '</h2>',
            $parsedown->parse(htmlspecialchars($shifttype->description)),
            $parsedown->parse(htmlspecialchars($shift->description)),
        ]),
    ]);

    if ($shift_admin) {
        $content[] = Shift_editor_info_render($shift);
    }

    $start = $shift->start->format(__('Y-m-d H:i'));

    return page_with_title(
        htmlspecialchars($shift->shiftType->name)
        . ' <small title="' . $start . '" data-countdown-ts="' . $shift->start->timestamp . '">%c</small>',
        $content
    );
}

/**
 * @param array                  $needed_angeltype
 * @param AngelType[]|Collection $angeltypes
 * @param Shift                  $shift
 * @param bool                   $user_shift_admin
 * @return string
 */
function Shift_view_render_needed_angeltype($needed_angeltype, $angeltypes, Shift $shift, $user_shift_admin)
{
    $angeltype = $angeltypes[$needed_angeltype['angel_type_id']];
    $angeltype_supporter = auth()->user()->isAngelTypeSupporter($angeltype)
        || auth()->can('admin_user_angeltypes');

    $needed_angels = '';

    $class = 'progress-bar-warning';
    if ($needed_angeltype['taken'] == 0) {
        $class = 'progress-bar-danger';
    }
    if ($needed_angeltype['taken'] >= $needed_angeltype['count']) {
        $class = 'progress-bar-success';
    }
    $needed_angels .= '<div class="list-group-item">';

    $needed_angels .= '<div class="float-end m-3">' . Shift_signup_button_render($shift, $angeltype) . '</div>';

    $needed_angels .= '<h3>' . AngelType_name_render($angeltype) . '</h3>';
    $bar_max = max($needed_angeltype['count'] * 10, $needed_angeltype['taken'] * 10, 10);
    $bar_value = max($bar_max / 10, $needed_angeltype['taken'] * 10);
    $needed_angels .= progress_bar(
        0,
        $bar_max,
        $bar_value,
        $class,
        $needed_angeltype['taken'] . ' / ' . $needed_angeltype['count']
    );

    $angels = [];
    foreach ($shift->shiftEntries as $shift_entry) {
        if ($shift_entry->angel_type_id == $needed_angeltype['angel_type_id']) {
            $angels[] = Shift_view_render_shift_entry($shift_entry, $user_shift_admin, $angeltype_supporter, $shift);
        }
    }

    $needed_angels .= join(', ', $angels);
    $needed_angels .= '</div>';

    return $needed_angels;
}

/**
 * @param ShiftEntry $shift_entry
 * @param bool  $user_shift_admin
 * @param bool  $angeltype_supporter
 * @param Shift $shift
 * @return string
 */
function Shift_view_render_shift_entry(ShiftEntry $shift_entry, $user_shift_admin, $angeltype_supporter, Shift $shift)
{
    $entry = User_Nick_render($shift_entry->user);
    if ($shift_entry->freeloaded) {
        $entry = '<del>' . $entry . '</del>';
    }
    $isUser = $shift_entry->user_id == auth()->user()->id;
    if ($user_shift_admin || $angeltype_supporter || $isUser) {
        $entry .= ' <div class="btn-group m-1">';
        if ($user_shift_admin || $isUser) {
            $entry .= button_icon(
                page_link_to('user_myshifts', ['edit' => $shift_entry->id, 'id' => $shift_entry->user_id]),
                'pencil',
                'btn-sm'
            );
        }
        $angeltype = $shift_entry->angelType;
        $disabled = Shift_signout_allowed($shift, $angeltype, $shift_entry->user_id) ? '' : ' btn-disabled';
        $entry .= button_icon(shift_entry_delete_link($shift_entry), 'trash', 'btn-sm' . $disabled);
        $entry .= '</div>';
    }
    return $entry;
}

/**
 * Calc shift length in format 12:23h.
 *
 * @param Shift $shift
 * @return string
 */
function shift_length(Shift $shift)
{
    $length = floor(($shift->end->timestamp - $shift->start->timestamp) / (60 * 60)) . ':';
    $length .= str_pad(
        (($shift->end->timestamp - $shift->start->timestamp) % (60 * 60)) / 60,
        2,
        '0',
        STR_PAD_LEFT
    );
    $length .= 'h';
    return $length;
}
