<?php

use Engelsystem\Config\GoodieType;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
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
 * @param Location $location
 * @return string HTML
 */
function Shift_view_header(Shift $shift, Location $location)
{
    return div('row', [
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('title.title') . '</h4>',
            '<p class="lead">'
            . ($shift->url != ''
                ? '<a href="' . htmlspecialchars($shift->url) . '">' . htmlspecialchars($shift->title) . '</a>'
                : htmlspecialchars($shift->title))
            . '</p>',
        ]),
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('shifts.start') . '</h4>',
            '<p class="lead' . (time() >= $shift->start->timestamp ? ' text-success' : '') . '">',
            icon('calendar-event') . dateWithEventDay($shift->start->format('Y-m-d')),
            '<br />',
            icon('clock') . $shift->start->format('H:i'),
            '</p>',
        ]),
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('shifts.end') . '</h4>',
            '<p class="lead' . (time() >= $shift->end->timestamp ? ' text-success' : '') . '">',
            icon('calendar-event') . dateWithEventDay($shift->end->format('Y-m-d')),
            '<br />',
            icon('clock') . $shift->end->format('H:i'),
            '</p>',
        ]),
        div('col-sm-3 col-xs-6', [
            '<h4>' . __('Location') . '</h4>',
            '<p class="lead">' . location_name_render($location) . '</p>',
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
            $shift->created_at->format(__('general.datetime')),
            User_Nick_render($shift->createdBy)
        );
    }
    if (!empty($shift->updated_by)) {
        $info[] = sprintf(
            icon('pencil') . __('edited at %s by %s'),
            $shift->updated_at->format(__('general.datetime')),
            User_Nick_render($shift->updatedBy)
        );
    }
    if ($shift->transaction_id) {
        $info[] = sprintf(
            icon('clock-history') . __('History ID: %s'),
            $shift->transaction_id
        );
    }
    if ($shift->schedule) {
        $angeltypeSource = $shift->schedule->needed_from_shift_type
            ? __(
                'shift.angeltype_source.shift_type',
                [
                    '<a href="' . url('/admin/schedule/edit/' . $shift->schedule->id) . '">'
                    . htmlspecialchars($shift->schedule->name)
                    . '</a>',
                    '<a href="' . url('/admin/shifttypes/' . $shift->shift_type_id) . '">'
                    . htmlspecialchars($shift->shiftType->name)
                    . '</a>',
                ]
            )
            : __('shift.angeltype_source.location', [
                '<a href="' . url('/admin/schedule/edit/' . $shift->schedule->id) . '">'
                . htmlspecialchars($shift->schedule->name)
                . '</a>',
                location_name_render($shift->location),
            ]);
    } else {
        $angeltypeSource = __('Shift');
    }
    $info[] = sprintf(__('shift.angeltype_source'), $angeltypeSource);
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
            url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]),
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
 * @param Location               $location
 * @param AngelType[]|Collection $angeltypes_source
 * @param ShiftSignupState       $shift_signup_state
 * @return string
 */
function Shift_view(
    Shift $shift,
    ShiftType $shifttype,
    Location $location,
    $angeltypes_source,
    ShiftSignupState $shift_signup_state
) {
    $shift_admin = auth()->can('admin_shifts');
    $user_shift_admin = auth()->can('user_shifts_admin');
    $admin_locations = auth()->can('admin_locations');
    $admin_shifttypes = auth()->can('shifttypes.view');
    $nightShiftsConfig = config('night_shifts');
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;

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
            // Additionally added angels (not required by shift)
            $needed_angels .= Shift_view_render_needed_angeltype([
                'angel_type_id' => $type,
                'count'         => 0,
                'restricted'    => true,
                'taken'         => $shiftEntry
                    ->where('angel_type_id', $type)
                    ->where('freeloaded', false)
                    ->count(),
            ], $angeltypes, $shift, $user_shift_admin);
        }
    }

    $content = [msg()];

    if ($shift_signup_state->getState() === ShiftSignupStatus::COLLIDES) {
        $content[] = info(__('This shift collides with one of your shifts.'), true);
    }

    if ($shift_signup_state->getState() === ShiftSignupStatus::SIGNED_UP) {
        $content[] = info(__('You are signed up for this shift.')
            . (($shift->start->subHours(config('last_unsubscribe')) < Carbon::now() && $shift->end > Carbon::now())
                ? ' ' . __('shift.sign_out.hint', [config('last_unsubscribe')])
                : ''), true);
    }

    if (config('signup_advance_hours') && $shift->start->timestamp > time() + config('signup_advance_hours') * 3600) {
        $content[] = info(sprintf(
            __('This shift is in the far future and becomes available for signup at %s.'),
            date(__('general.datetime'), $shift->start->timestamp - config('signup_advance_hours') * 3600)
        ), true);
    }

    $buttons = [];
    if ($shift_admin || $admin_shifttypes || $admin_locations) {
        $buttons = [
            $shift_admin ? button(shift_edit_link($shift), icon('pencil'), '', '', __('form.edit')) : '',
            $shift_admin ? form([
                form_hidden('delete_shift', $shift->id),
                form_submit(
                    'delete',
                    icon('trash'),
                    '',
                    false,
                    'danger',
                    __('form.delete'),
                    [
                        'confirm_submit_title' => __('Do you want to delete the shift "%s" from %s to %s?', [
                            $shift->shiftType->name,
                            $shift->start->format(__('general.datetime')),
                            $shift->end->format(__('H:i')),
                        ]),
                        'confirm_button_text' => icon('trash') . __('form.delete'),
                    ]
                ),
            ], url('/user-shifts', ['delete_shift' => $shift->id])) : '',
            $admin_shifttypes
                ? button(url('/admin/shifttypes/' . $shifttype->id), htmlspecialchars($shifttype->name))
                : '',
            $admin_locations
                ? button(
                    location_link($location),
                    icon('pin-map-fill') . htmlspecialchars($location->name)
                )
                : '',
        ];
    }
    $buttons[] = button(
        user_link(auth()->user()->id),
        '<span class="icon-icon_angel"></span> ' . __('profile.my-shifts')
    );
    $content[] = buttons($buttons);

    $content[] = Shift_view_header($shift, $location);
    $content[] = div('row', [
        div('col-sm-6', [
            '<h2>' . __('Needed angels') . '</h2>',
            '<div class="list-group">' . $needed_angels . '</div>',
        ]),
        div('col-sm-6', [
            '<h2>' . __('general.description') . '</h2>',
            $parsedown->parse(htmlspecialchars($shifttype->description)),
            $parsedown->parse(htmlspecialchars($shift->description)),
        ]),
    ]);

    if ($shift_admin) {
        $content[] = Shift_editor_info_render($shift);
    }

    $start = $shift->start->format(__('general.datetime'));

    $night_shift_hint = '';
    if ($shift->isNightShift() && $goodie_enabled) {
        $night_shift_hint = ' <small><span class="bi bi-moon-stars text-info" data-bs-toggle="tooltip" title="'
            . __('Night shifts between %d and %d am are multiplied by %d for the %s score.', [
                $nightShiftsConfig['start'],
                $nightShiftsConfig['end'],
                $nightShiftsConfig['multiplier'],
                ($goodie_tshirt ? __('T-shirt') : __('goodie'))])
            . '"></span></small>';
    }
    $link = button(url('/user-shifts'), icon('chevron-left'), 'btn-sm', '', __('general.back'));
    return page_with_title(
        $link . ' '
        . htmlspecialchars($shift->shiftType->name)
        . ' <small title="' . $start . '" data-countdown-ts="' . $shift->start->timestamp . '">%c</small>'
        . $night_shift_hint,
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
        $entry .= button_icon(
            url('/user-myshifts', ['edit' => $shift_entry->id, 'id' => $shift_entry->user_id]),
            'pencil',
            'btn-sm',
            __('form.edit')
        );
        $angeltype = $shift_entry->angelType;
        $disabled = Shift_signout_allowed($shift, $angeltype, $shift_entry->user_id) ? '' : ' btn-disabled';
        $entry .= button_icon(
            shift_entry_delete_link($shift_entry),
            'trash',
            'btn-sm btn-danger' . $disabled,
            __('form.delete'),
            !Shift_signout_allowed($shift, $angeltype, $shift_entry->user_id)
        );
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
