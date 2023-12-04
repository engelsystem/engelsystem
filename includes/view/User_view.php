<?php

use Carbon\Carbon;
use Engelsystem\Config\GoodieType;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Group;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Gui for deleting user with password field.
 *
 * @param User $user
 * @return string
 */
function User_delete_view($user)
{
    return page_with_title(sprintf(__('Delete %s'), User_Nick_render($user)), [
        msg(),
        buttons([
            button(user_edit_link($user->id), icon('chevron-left') . __('back')),
        ]),
        error(
            __('Do you really want to delete the user including all his shifts and every other piece of his data?'),
            true
        ),
        form([
            form_password('password', __('Your password'), 'current-password'),
            form_submit('submit', __('Delete')),
        ]),
    ]);
}

/**
 * View for editing the number of given vouchers
 *
 * @param User $user
 * @return string
 */
function User_edit_vouchers_view($user)
{
    return page_with_title(sprintf(__('%s\'s vouchers'), User_Nick_render($user)), [
        msg(),
        buttons([
            button(user_link($user->id), icon('chevron-left') . __('back')),
        ]),
        info(sprintf(
            $user->state->force_active
                ? __('Angel can receive another %d vouchers and is FA.')
                : __('Angel can receive another %d vouchers.'),
            User_get_eligable_voucher_count($user)
        ), true),
        form(
            [
                form_spinner('vouchers', __('Number of vouchers given out'), $user->state->got_voucher),
                form_submit('submit', __('Save')),
            ],
            page_link_to('users', ['action' => 'edit_vouchers', 'user_id' => $user->id])
        ),
    ]);
}

/**
 * @param User[] $users
 * @param string $order_by
 * @param int    $arrived_count
 * @param int    $active_count
 * @param int    $force_active_count
 * @param int    $freeloads_count
 * @param int    $tshirts_count
 * @param int    $voucher_count
 * @return string
 */
function Users_view(
    $users,
    $order_by,
    $arrived_count,
    $active_count,
    $force_active_count,
    $freeloads_count,
    $tshirts_count,
    $voucher_count
) {
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;
    $usersList = [];
    foreach ($users as $user) {
        $u = [];
        $u['name'] = User_Nick_render($user) . User_Pronoun_render($user);
        $u['first_name'] = htmlspecialchars((string) $user->personalData->first_name);
        $u['last_name'] = htmlspecialchars((string) $user->personalData->last_name);
        $u['dect'] = sprintf('<a href="tel:%s">%1$s</a>', htmlspecialchars((string) $user->contact->dect));
        $u['arrived'] = icon_bool($user->state->arrived);
        if (config('enable_voucher')) {
            $u['got_voucher'] = $user->state->got_voucher;
        }
        $u['freeloads'] = $user->getAttribute('freeloads');
        $u['active'] = icon_bool($user->state->active);
        $u['force_active'] = icon_bool($user->state->force_active);
        if ($goodie_enabled) {
            $u['got_shirt'] = icon_bool($user->state->got_shirt);
            if ($goodie_tshirt) {
                $u['shirt_size'] = $user->personalData->shirt_size;
            }
        }
        $u['arrival_date'] = $user->personalData->planned_arrival_date
            ? $user->personalData->planned_arrival_date->format(__('Y-m-d')) : '';
        $u['departure_date'] = $user->personalData->planned_departure_date
            ? $user->personalData->planned_departure_date->format(__('Y-m-d')) : '';
        $u['last_login_at'] = $user->last_login_at ? $user->last_login_at->format(__('m/d/Y h:i a')) : '';
        $u['actions'] = table_buttons([
            button_icon(page_link_to('admin_user', ['id' => $user->id]), 'pencil', 'btn-sm'),
        ]);
        $usersList[] = $u;
    }
    $usersList[] = [
        'name'         => '<strong>' . __('Sum') . '</strong>',
        'arrived'      => $arrived_count,
        'got_voucher'  => $voucher_count,
        'active'       => $active_count,
        'force_active' => $force_active_count,
        'freeloads'    => $freeloads_count,
        'got_shirt'    => $tshirts_count,
        'actions'      => '<strong>' . count($usersList) . '</strong>',
    ];

    $user_table_headers = [];

    if (!config('display_full_name')) {
        $user_table_headers['name'] = Users_table_header_link('name', __('Nick'), $order_by);
    }
    if (config('enable_user_name')) {
        $user_table_headers['first_name'] = Users_table_header_link('first_name', __('Prename'), $order_by);
        $user_table_headers['last_name'] = Users_table_header_link('last_name', __('Name'), $order_by);
    }
    if (config('enable_dect')) {
        $user_table_headers['dect'] = Users_table_header_link('dect', __('DECT'), $order_by);
    }
    $user_table_headers['arrived'] = Users_table_header_link('arrived', __('Arrived'), $order_by);
    if (config('enable_voucher')) {
        $user_table_headers['got_voucher'] = Users_table_header_link('got_voucher', __('Voucher'), $order_by);
    }
    $user_table_headers['freeloads'] = Users_table_header_link('freeloads', __('Freeloads'), $order_by);
    $user_table_headers['active'] = Users_table_header_link('active', __('Active'), $order_by);
    $user_table_headers['force_active'] = Users_table_header_link('force_active', __('Forced'), $order_by);
    if ($goodie_enabled) {
        if ($goodie_tshirt) {
            $user_table_headers['got_shirt'] = Users_table_header_link('got_shirt', __('T-Shirt'), $order_by);
            $user_table_headers['shirt_size'] = Users_table_header_link('shirt_size', __('Size'), $order_by);
        } else {
            $user_table_headers['got_shirt'] = Users_table_header_link('got_shirt', __('Goodie'), $order_by);
        }
    }
    $user_table_headers['arrival_date'] = Users_table_header_link(
        'planned_arrival_date',
        __('Planned arrival'),
        $order_by
    );
    $user_table_headers['departure_date'] = Users_table_header_link(
        'planned_departure_date',
        __('Planned departure'),
        $order_by
    );
    $user_table_headers['last_login_at'] = Users_table_header_link('last_login_at', __('Last login'), $order_by);
    $user_table_headers['actions'] = '';

    foreach (config('disabled_user_view_columns') ?? [] as $key) {
        unset($user_table_headers[$key]);
    }

    return page_with_title(__('All users'), [
        msg(),
        buttons([
            button(page_link_to('register'), icon('plus-lg') . __('New user')),
        ]),
        table($user_table_headers, $usersList),
    ]);
}

/**
 * @param string $column
 * @param string $label
 * @param string $order_by
 * @return string
 */
function Users_table_header_link($column, $label, $order_by)
{
    return '<a href="'
        . page_link_to('users', ['OrderBy' => $column])
        . '">'
        . $label . ($order_by == $column ? ' <span class="caret"></span>' : '')
        . '</a>';
}

/**
 * @param User $user
 * @return string|false
 */
function User_shift_state_render($user)
{
    if (!$user->state->arrived) {
        return '';
    }

    $upcoming_shifts = ShiftEntries_upcoming_for_user($user);
    if ($upcoming_shifts->isEmpty()) {
        return '<span class="text-success">' . __('Free') . '</span>';
    }

    /** @var ShiftEntry $nextShiftEntry */
    $nextShiftEntry = $upcoming_shifts->first();

    $start = $nextShiftEntry->shift->start;
    $end = $nextShiftEntry->shift->end;
    $startFormat = $start->format(__('Y-m-d H:i'));
    $endFormat = $end->format(__('Y-m-d H:i'));
    $startTimestamp = $start->timestamp;
    $endTimestamp = $end->timestamp;

    if ($startTimestamp > time()) {
        if ($startTimestamp - time() > 3600) {
            return '<span class="text-success" title="' . $startFormat . '" data-countdown-ts="' . $startTimestamp . '">'
                . __('Next shift %c')
                . '</span>';
        }
        return '<span class="text-warning" title="' . $startFormat . '" data-countdown-ts="' . $startTimestamp . '">'
            . __('Next shift %c')
            . '</span>';
    }

    $halfway = ($startTimestamp + $endTimestamp) / 2;
    if (time() < $halfway) {
        return '<span class="text-danger" title="' . $startFormat . '" data-countdown-ts="' . $startTimestamp . '">'
            . __('Shift started %c')
            . '</span>';
    }

    return '<span class="text-danger" title="' . $endFormat . '" data-countdown-ts="' . $endTimestamp . '">'
        . __('Shift ends %c')
        . '</span>';
}

function User_last_shift_render($user)
{
    if (!$user->state->arrived) {
        return '';
    }

    $last_shifts = ShiftEntries_finished_by_user($user);
    if ($last_shifts->isEmpty()) {
        return '';
    }

    /** @var ShiftEntry $lastShiftEntry */
    $lastShiftEntry = $last_shifts->first();
    $end = $lastShiftEntry->shift->end;

    return '<span title="' . $end->format(__('Y-m-d H:i')) . '" data-countdown-ts="' . $end->timestamp . '">'
        . __('Shift ended %c')
        . '</span>';
}

/**
 * @param array $needed_angel_type
 * @return string
 */
function User_view_shiftentries($needed_angel_type)
{
    $shift_info = '<br><b><a href="'
        . page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $needed_angel_type['id']])
        . '">' . htmlspecialchars($needed_angel_type['name']) . '</a>:</b> ';

    $shift_entries = [];
    foreach ($needed_angel_type['users'] as $user_shift) {
        $member = User_Nick_render($user_shift);
        if ($user_shift['freeloaded']) {
            $member = '<del>' . $member . '</del>';
        }

        $shift_entries[] = $member;
    }
    $shift_info .= join(', ', $shift_entries);

    return $shift_info;
}

/**
 * Helper that renders a shift line for user view
 *
 * @param Shift $shift
 * @param User  $user_source
 * @param bool  $its_me
 * @return array
 */
function User_view_myshift(Shift $shift, $user_source, $its_me)
{
    $shift_info = '<a href="' . shift_link($shift) . '">' . htmlspecialchars($shift->shiftType->name) . '</a>';
    if ($shift->title) {
        $shift_info .= '<br /><a href="' . shift_link($shift) . '">' . htmlspecialchars($shift->title) . '</a>';
    }
    foreach ($shift->needed_angeltypes as $needed_angel_type) {
        $shift_info .= User_view_shiftentries($needed_angel_type);
    }

    $myshift = [
        'date'       => icon('calendar-event')
            . $shift->start->format(__('Y-m-d')) . '<br>'
            . icon('clock-history') . $shift->start->format('H:i')
            . ' - '
            . $shift->end->format(__('H:i')),
        'duration'   => sprintf('%.2f', ($shift->end->timestamp - $shift->start->timestamp) / 3600) . '&nbsp;h',
        'room'       => Room_name_render($shift->room),
        'shift_info' => $shift_info,
        'comment'    => '',
    ];

    if ($its_me) {
        $myshift['comment'] = htmlspecialchars($shift->user_comment);
    }

    if ($shift->freeloaded) {
        $myshift['duration'] = '<p class="text-danger">'
            . sprintf('%.2f', -($shift->end->timestamp - $shift->start->timestamp) / 3600 * 2) . '&nbsp;h'
            . '</p>';
        if (auth()->can('user_shifts_admin')) {
            $myshift['comment'] .= '<br />'
                . '<p class="text-danger">'
                . __('Freeloaded') . ': ' . htmlspecialchars($shift->freeloaded_comment)
                . '</p>';
        } else {
            $myshift['comment'] .= '<br /><p class="text-danger">' . __('Freeloaded') . '</p>';
        }
    }

    $myshift['actions'] = [
        button(shift_link($shift), icon('eye') . __('view'), 'btn-sm'),
    ];
    if ($its_me || auth()->can('user_shifts_admin')) {
        $myshift['actions'][] = button(
            page_link_to('user_myshifts', ['edit' => $shift->shift_entry_id, 'id' => $user_source->id]),
            icon('pencil') . __('edit'),
            'btn-sm'
        );
    }

    if (Shift_signout_allowed($shift, (new AngelType())->forceFill(['id' => $shift->angel_type_id]), $user_source->id)) {
        $myshift['actions'][] = button(
            shift_entry_delete_link($shift),
            icon('trash') . __('sign off'),
            'btn-sm'
        );
    }
    $myshift['actions'] = table_buttons($myshift['actions']);

    return $myshift;
}

/**
 * Helper that prepares the shift table for user view
 *
 * @param Shift[]|Collection   $shifts
 * @param User                 $user_source
 * @param bool                 $its_me
 * @param int                  $tshirt_score
 * @param bool                 $tshirt_admin
 * @param Worklog[]|Collection $user_worklogs
 * @param bool                 $admin_user_worklog_privilege
 *
 * @return array
 */
function User_view_myshifts(
    $shifts,
    $user_source,
    $its_me,
    $tshirt_score,
    $tshirt_admin,
    $user_worklogs,
    $admin_user_worklog_privilege
) {
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;
    $myshifts_table = [];
    $timeSum = 0;
    foreach ($shifts as $shift) {
        $key = $shift->start->timestamp . '-shift-' . $shift->shift_entry_id . $shift->id;
        $myshifts_table[$key] = User_view_myshift($shift, $user_source, $its_me);

        if (!$shift->freeloaded) {
            $timeSum += ($shift->end->timestamp - $shift->start->timestamp);
        }
    }

    foreach ($user_worklogs as $worklog) {
        $key = $worklog->worked_at->timestamp . '-worklog-' . $worklog->id;
        $myshifts_table[$key] = User_view_worklog($worklog, $admin_user_worklog_privilege);
        $timeSum += $worklog->hours * 3600;
    }

    if (count($myshifts_table) > 0) {
        ksort($myshifts_table);
        $myshifts_table[] = [
            'date'       => '<b>' . __('Sum:') . '</b>',
            'duration'   => '<b>' . sprintf('%.2f', round($timeSum / 3600, 2)) . '&nbsp;h</b>',
            'room'       => '',
            'shift_info' => '',
            'comment'    => '',
            'actions'    => '',
        ];
        if ($goodie_enabled && ($its_me || $tshirt_admin)) {
            $myshifts_table[] = [
                'date'       => '<b>' . ($goodie_tshirt ? __('Your t-shirt score') : __('Your goodie score')) . '&trade;:</b>',
                'duration'   => '<b>' . $tshirt_score . '</b>',
                'room'       => '',
                'shift_info' => '',
                'comment'    => '',
                'actions'    => '',
            ];
        }
    }
    return $myshifts_table;
}

/**
 * Renders table entry for user work log
 *
 * @param Worklog $worklog
 * @param bool    $admin_user_worklog_privilege
 * @return array
 */
function User_view_worklog(Worklog $worklog, $admin_user_worklog_privilege)
{
    $actions = '';
    if ($admin_user_worklog_privilege) {
        $actions = table_buttons([
            button(
                url('/admin/user/' . $worklog->user->id . '/worklog/' . $worklog->id),
                icon('pencil') . __('edit'),
                'btn-sm'
            ),
            button(
                url('/admin/user/' . $worklog->user->id . '/worklog/' . $worklog->id . '/delete'),
                icon('trash') . __('delete'),
                'btn-sm'
            ),
        ]);
    }

    return [
        'date'       => icon('calendar-event') . date(__('Y-m-d'), $worklog->worked_at->timestamp),
        'duration'   => sprintf('%.2f', $worklog->hours) . ' h',
        'room'       => '',
        'shift_info' => __('Work log entry'),
        'comment'    => htmlspecialchars($worklog->comment) . '<br>'
            . sprintf(
                __('Added by %s at %s'),
                User_Nick_render($worklog->creator),
                $worklog->created_at->format(__('Y-m-d H:i'))
            ),
        'actions'    => $actions,
    ];
}

/**
 * Renders view for a single user
 *
 * @param User                 $user_source
 * @param bool                 $admin_user_privilege
 * @param bool                 $freeloader
 * @param AngelType[]          $user_angeltypes
 * @param Group[]              $user_groups
 * @param Shift[]|Collection   $shifts
 * @param bool                 $its_me
 * @param int                  $tshirt_score
 * @param bool                 $tshirt_admin
 * @param bool                 $admin_user_worklog_privilege
 * @param Worklog[]|Collection $user_worklogs
 *
 * @return string
 */
function User_view(
    $user_source,
    $admin_user_privilege,
    $freeloader,
    $user_angeltypes,
    $user_groups,
    $shifts,
    $its_me,
    $tshirt_score,
    $tshirt_admin,
    $admin_user_worklog_privilege,
    $user_worklogs
) {
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;
    $auth = auth();
    $nightShiftsConfig = config('night_shifts');
    $user_name = htmlspecialchars(
        $user_source->personalData->first_name
    ) . ' ' . htmlspecialchars($user_source->personalData->last_name);
    $myshifts_table = '';
    if ($its_me || $admin_user_privilege || $tshirt_admin) {
        $my_shifts = User_view_myshifts(
            $shifts,
            $user_source,
            $its_me,
            $tshirt_score,
            $tshirt_admin,
            $user_worklogs,
            $admin_user_worklog_privilege
        );
        if (count($my_shifts) > 0) {
            $myshifts_table = div('table-responsive', table([
                'date'       => __('Day &amp; time'),
                'duration'   => __('Duration'),
                'room'       => __('Location'),
                'shift_info' => __('Name &amp; workmates'),
                'comment'    => __('Comment'),
                'actions'    => __('Action'),
            ], $my_shifts));
        } elseif ($user_source->state->force_active) {
            $myshifts_table = success(__('You have done enough.'), true);
        }
    }

    $needs_drivers_license = false;
    foreach ($user_angeltypes as $angeltype) {
        $needs_drivers_license = $needs_drivers_license || $angeltype->requires_driver_license;
    }

    $needs_ifsg_certificate = false;
    foreach ($user_angeltypes as $angeltype) {
        $needs_ifsg_certificate = $needs_ifsg_certificate || $angeltype->requires_ifsg_certificate;
    }

    return page_with_title(
        '<span class="icon-icon_angel"></span> '
        . (
        (config('enable_pronoun') && $user_source->personalData->pronoun)
            ? '<small>' . htmlspecialchars($user_source->personalData->pronoun) . '</small> '
            : ''
        )
        . htmlspecialchars($user_source->name)
        . (config('enable_user_name') ? ' <small>' . $user_name . '</small>' : ''),
        [
            msg(),
            div('row', [
                div('col-md-12', [
                    table_buttons([
                        $auth->can('user.edit.shirt') && $goodie_enabled ? button(
                            url('/admin/user/' . $user_source->id . '/goodie'),
                            icon('person') . ($goodie_tshirt ? __('Shirt') : __('Goodie'))
                        ) : '',
                        $admin_user_privilege ? button(
                            page_link_to('admin_user', ['id' => $user_source->id]),
                            icon('pencil') . __('edit')
                        ) : '',
                        $admin_user_privilege || ($its_me && $needs_drivers_license) ? button(
                            user_driver_license_edit_link($user_source),
                            icon('person-vcard') . __('driving license')
                        ) : '',
                        config('ifsg_enabled') && ($admin_user_privilege || ($its_me && $needs_ifsg_certificate)) ? button(
                            page_link_to('settings/certificates'),
                            icon('card-checklist') . __('ifsg.certificate')
                        ) : '',
                        (($admin_user_privilege || $auth->can('admin_arrive')) && !$user_source->state->arrived) ?
                            form([
                                form_hidden('action', 'arrived'),
                                form_hidden('user', $user_source->id),
                                form_submit('submit', __('arrived'), '', false),
                            ], page_link_to('admin_arrive'), true) : '',
                        ($admin_user_privilege || $auth->can('voucher.edit')) && config('enable_voucher') ?
                            button(
                                page_link_to(
                                    'users',
                                    ['action' => 'edit_vouchers', 'user_id' => $user_source->id]
                                ),
                                icon('valentine') . __('Vouchers')
                            )
                            : '',
                        $admin_user_worklog_privilege ? button(
                            url('/admin/user/' . $user_source->id . '/worklog'),
                            icon('clock-history') . __('worklog.add')
                        ) : '',
                    ], 'mb-2'),
                    $its_me ? table_buttons([
                        button(
                            page_link_to('settings/profile'),
                            icon('person-fill-gear') . __('Settings')
                        ),
                        $auth->can('ical') ? button(
                            page_link_to('ical', ['key' => $user_source->api_key]),
                            icon('calendar-week') . __('iCal Export')
                        ) : '',
                        $auth->can('shifts_json_export') ? button(
                            page_link_to('shifts_json_export', ['key' => $user_source->api_key]),
                            icon('braces') . __('JSON Export')
                        ) : '',
                        (
                            $auth->can('shifts_json_export')
                            || $auth->can('ical')
                            || $auth->can('atom')
                        ) ? button(
                            page_link_to('user_myshifts', ['reset' => 1]),
                            icon('arrow-repeat') . __('Reset API key')
                        ) : '',
                    ], 'mb-2') : '',
                ]),
            ]),
            div('row user-info', [
                div('col-md-2', [
                    config('enable_dect') && $user_source->contact->dect ?
                        heading(
                            icon('phone')
                            . ' <a href="tel:' . htmlspecialchars($user_source->contact->dect) . '">'
                            . htmlspecialchars($user_source->contact->dect)
                            . '</a>'
                        )
                        : '',
                    config('enable_mobile_show') && $user_source->contact->mobile ?
                        $user_source->settings->mobile_show ?
                            heading(
                                icon('phone')
                                . ' <a href="tel:' . htmlspecialchars($user_source->contact->mobile) . '">'
                                . htmlspecialchars($user_source->contact->mobile)
                                . '</a>'
                            )
                            : ''
                        : '',
                    $auth->can('user_messages') ?
                        heading(
                            '<a href="' . page_link_to('/messages/' . $user_source->id) . '">'
                            . icon('envelope')
                            . '</a>'
                        )
                        : '',
                ]),
                User_view_state($admin_user_privilege, $freeloader, $user_source),
                User_angeltypes_render($user_angeltypes),
                User_groups_render($user_groups),
                $admin_user_privilege ? User_oauth_render($user_source) : '',
            ]),
            ($its_me || $admin_user_privilege) ? '<h2>' . __('Shifts') . '</h2>' : '',
            $myshifts_table,
            ($its_me && $nightShiftsConfig['enabled'] && $goodie_enabled) ? info(
                sprintf(
                    icon('info-circle') . __('Your night shifts between %d and %d am count twice.'),
                    $nightShiftsConfig['start'],
                    $nightShiftsConfig['end']
                ),
                true,
                true
            ) : '',
            $its_me && count($shifts) == 0
                ? error(sprintf(
                    __('Go to the <a href="%s">shifts table</a> to sign yourself up for some shifts.'),
                    page_link_to('user_shifts')
                ), true, true)
                : '',
            $its_me ? ical_hint() : '',
        ]
    );
}

/**
 * Render the state section of user view
 *
 * @param bool $admin_user_privilege
 * @param bool $freeloader
 * @param User $user_source
 * @return string
 */
function User_view_state($admin_user_privilege, $freeloader, $user_source)
{
    if ($admin_user_privilege) {
        $state = User_view_state_admin($freeloader, $user_source);
    } else {
        $state = User_view_state_user($user_source);
    }

    return div('col-md-2', [
        heading(__('User state'), 4),
        join('<br>', $state),
    ]);
}

/**
 * Render the state section of user view for users.
 *
 * @param User $user_source
 * @return array
 */
function User_view_state_user($user_source)
{
    $state = [
        User_shift_state_render($user_source),
    ];

    if ($user_source->state->arrived) {
        $state[] = '<span class="text-success">' . icon('house') . __('Arrived') . '</span>';
    } else {
        $state[] = '<span class="text-danger">' . __('Not arrived') . '</span>';
    }

    return $state;
}


/**
 * Render the state section of user view for admins.
 *
 * @param bool $freeloader
 * @param User $user_source
 * @return array
 */
function User_view_state_admin($freeloader, $user_source)
{
    $state = [];
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;

    if ($freeloader) {
        $state[] = '<span class="text-danger">' . icon('exclamation-circle') . __('Freeloader') . '</span>';
    }

    $state[] = User_shift_state_render($user_source);

    if ($user_source->state->arrived) {
        $state[] = '<span class="text-success">' . icon('house')
            . sprintf(
                __('Arrived at %s'),
                $user_source->state->arrival_date ? $user_source->state->arrival_date->format(__('Y-m-d')) : ''
            )
            . '</span>';

        if ($user_source->state->force_active) {
            $state[] = '<span class="text-success">' . __('Active (forced)') . '</span>';
        } elseif ($user_source->state->active) {
            $state[] = '<span class="text-success">' . __('Active') . '</span>';
        }
        if ($user_source->state->got_shirt && $goodie_enabled) {
            $state[] = '<span class="text-success">' . ($goodie_tshirt ? __('T-Shirt') : __('Goodie')) . '</span>';
        }
    } else {
        $arrivalDate = $user_source->personalData->planned_arrival_date;
        $state[] = '<span class="text-danger">'
            . ($arrivalDate ? sprintf(
                __('Not arrived (Planned: %s)'),
                $arrivalDate->format(__('Y-m-d'))
            ) : __('Not arrived'))
            . '</span>';
    }

    if (config('enable_voucher')) {
        $voucherCount = $user_source->state->got_voucher;
        $availableCount = $voucherCount + User_get_eligable_voucher_count($user_source);
        $availableCount = max($voucherCount, $availableCount);
        if ($user_source->state->got_voucher > 0) {
            $state[] = '<span class="text-success">'
                . icon('valentine')
                . __('Got %s of %s vouchers', [$voucherCount, $availableCount])
                . '</span>';
        } else {
            $state[] = '<span class="text-danger">'
                . __('Got no vouchers')
                . ($availableCount ? ' (' . __('out of %s', [$availableCount]) . ')' : '')
                . '</span>';
        }
    }

    return $state;
}

/**
 * @param AngelType[] $user_angeltypes
 * @return string
 */
function User_angeltypes_render($user_angeltypes)
{
    $output = [];
    foreach ($user_angeltypes as $angeltype) {
        $class = 'text-success';
        if ($angeltype->restricted && !$angeltype->pivot->confirm_user_id) {
            $class = 'text-warning';
        }
        $output[] = '<a href="' . angeltype_link($angeltype->id) . '" class="' . $class . '">'
            . ($angeltype->pivot->supporter ? icon('patch-check') : '') . htmlspecialchars($angeltype->name)
            . '</a>';
    }
    return div('col-md-2', [
        heading(__('Angeltypes'), 4),
        join('<br>', $output),
    ]);
}

/**
 * @param Group[] $user_groups
 * @return string
 */
function User_groups_render($user_groups)
{
    $output = [];
    foreach ($user_groups as $group) {
        $output[] = __(htmlspecialchars($group->name));
    }

    return div('col-md-2', [
        '<h4>' . __('Rights') . '</h4>',
        join('<br>', $output),
    ]);
}

/**
 * @param User $user
 * @return string
 */
function User_oauth_render(User $user)
{
    $config = config('oauth');

    $output = [];
    foreach ($user->oauth as $oauth) {
        $output[] = __(
            htmlspecialchars(
                isset($config[$oauth->provider]['name'])
                    ? $config[$oauth->provider]['name']
                    : Str::ucfirst($oauth->provider)
            )
        );
    }

    if (!$output) {
        return '';
    }

    return div('col-md-2', [
        heading(__('OAuth'), 4),
        join('<br>', $output),
    ]);
}

/**
 * Render a user nickname.
 *
 * @param array|User $user
 * @param bool       $plain
 * @return string
 */
function User_Nick_render($user, $plain = false)
{
    if (is_array($user)) {
        $user = (new User())->forceFill($user);
    }

    if ($plain) {
        return sprintf('%s (%u)', $user->displayName, $user->id);
    }

    return render_profile_link(
        '<span class="icon-icon_angel"></span> ' . htmlspecialchars($user->displayName) . '</a>',
        $user->id,
        ($user->state->arrived ? '' : 'text-muted')
    );
}

/**
 * Format the user pronoun
 *
 * @param User $user
 * @return string
 */
function User_Pronoun_render(User $user): string
{
    if (!config('enable_pronoun') || !$user->personalData->pronoun) {
        return '';
    }

    return ' (' . htmlspecialchars($user->personalData->pronoun) . ')';
}

/**
 * @param string $text
 * @param int    $user_id
 * @param string $class
 * @return string
 */
function render_profile_link($text, $user_id = null, $class = '')
{
    $profile_link = page_link_to('settings/profile');
    if (!is_null($user_id)) {
        $profile_link = page_link_to('users', ['action' => 'view', 'user_id' => $user_id]);
    }

    return sprintf(
        '<a class="%s" href="%s">%s</a>',
        $class,
        $profile_link,
        $text
    );
}

/**
 * @return string|null
 */
function render_user_departure_date_hint()
{
    if (config('enable_planned_arrival') && !auth()->user()->personalData->planned_departure_date) {
        $text = __('Please enter your planned date of departure on your settings page to give us a feeling for teardown capacities.');
        return render_profile_link($text, null, 'text-danger');
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_freeloader_hint()
{
    if (auth()->user()->isFreeloader()) {
        return sprintf(
            __('You freeloaded at least %s shifts. Shift signup is locked. Please go to heavens desk to be unlocked again.'),
            config('max_freeloadable_shifts')
        );
    }

    return null;
}

/**
 * Hinweis für Engel, die noch nicht angekommen sind
 *
 * @return string|null
 */
function render_user_arrived_hint()
{
    if (config('signup_requires_arrival') && !auth()->user()->state->arrived) {
        /** @var Carbon $buildup */
        $buildup = config('buildup_start');
        if (!empty($buildup) && $buildup->lessThan(new Carbon())) {
            return __('You are not marked as arrived. Please go to heaven\'s desk, get your angel badge and/or tell them that you arrived already.');
        }
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_tshirt_hint()
{
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_tshirt = $goodie === GoodieType::Tshirt;
    if ($goodie_tshirt && !auth()->user()->personalData->shirt_size) {
        $text = __('You need to specify a tshirt size in your settings!');
        return render_profile_link($text);
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_dect_hint()
{
    $user = auth()->user();
    if (
        $user->state->arrived
        && config('enable_dect') && !$user->contact->dect
    ) {
        $text = __('You need to specify a DECT phone number in your settings! If you don\'t have a DECT phone, just enter \'-\'.');
        return render_profile_link($text);
    }

    return null;
}
