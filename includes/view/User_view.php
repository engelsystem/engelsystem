<?php

use Carbon\Carbon;
use Engelsystem\Config\GoodieType;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Group;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\PasswordReset;
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
    $link = button(user_edit_link($user->id), icon('chevron-left'), 'btn-sm', '', __('general.back'));
    return page_with_title($link . ' ' . sprintf(__('Delete %s'), User_Nick_render($user)), [
        msg(),
        error(
            __('Do you really want to delete the user including all his shifts and every other piece of his data?'),
            true
        ),
        form([
            form_password('password', __('Your password'), 'current-password'),
            form_submit('submit', __('form.delete')),
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
    $link = button(user_link($user->id), icon('chevron-left'), 'btn-sm', '', __('general.back'));
    return page_with_title(
        $link . ' ' . sprintf(__('%s\'s vouchers'), User_Nick_render($user)),
        [
            msg(),
            info(sprintf(
                $user->state->force_active && config('enable_force_active')
                    ? __('Angel can receive another %d vouchers and is FA.')
                    : __('Angel can receive another %d vouchers.'),
                User_get_eligable_voucher_count($user)
            ), true),
            form(
                [
                    form_spinner('vouchers', __('Number of vouchers given out'), $user->state->got_voucher),
                    form_submit('submit', icon('save') . __('form.save')),
                ],
                url('/users', ['action' => 'edit_vouchers', 'user_id' => $user->id])
            ),
        ]
    );
}

/**
 * @param User[] $users
 * @param string $order_by
 * @param int    $arrived_count
 * @param int    $active_count
 * @param int    $force_active_count
 * @param int    $freeloads_count
 * @param int    $goodies_count
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
    $goodies_count,
    $voucher_count
) {
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;
    $usersList = [];
    foreach ($users as $user) {
        $u = [];
        $u['name'] = User_Nick_render($user)
            . User_Pronoun_render($user)
            . user_info_icon($user);
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
            $u['got_goodie'] = icon_bool($user->state->got_goodie);
            if ($goodie_tshirt) {
                $u['shirt_size'] = $user->personalData->shirt_size;
            }
        }
        $u['arrival_date'] = $user->personalData->planned_arrival_date
            ? $user->personalData->planned_arrival_date->format(__('general.date')) : '';
        $u['departure_date'] = $user->personalData->planned_departure_date
            ? $user->personalData->planned_departure_date->format(__('general.date')) : '';
        $u['last_login_at'] = $user->last_login_at ? $user->last_login_at->format(__('general.datetime')) : '';
        $u['actions'] = table_buttons([
            button(
                url(
                    '/admin-user',
                    ['id' => $user->id]
                ),
                icon('pencil'),
                'btn-sm',
                '',
                __('form.edit')
            ),
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
        'got_goodie'   => $goodies_count,
        'actions'      => '<strong>' . count($usersList) . '</strong>',
    ];

    $user_table_headers = [];

    if (!config('display_full_name')) {
        $user_table_headers['name'] = Users_table_header_link('name', __('general.nick'), $order_by);
    }
    if (config('enable_full_name')) {
        $user_table_headers['first_name'] = Users_table_header_link('first_name', __('settings.profile.firstname'), $order_by);
        $user_table_headers['last_name'] = Users_table_header_link('last_name', __('settings.profile.lastname'), $order_by);
    }
    if (config('enable_dect')) {
        $user_table_headers['dect'] = Users_table_header_link('dect', __('general.dect'), $order_by);
    }
    $user_table_headers['arrived'] = Users_table_header_link('arrived', __('Arrived'), $order_by);
    if (config('enable_voucher')) {
        $user_table_headers['got_voucher'] = Users_table_header_link('got_voucher', __('Voucher'), $order_by);
    }
    $user_table_headers['freeloads'] = Users_table_header_link('freeloads', __('Freeloads'), $order_by);
    $user_table_headers['active'] = Users_table_header_link('active', __('user.active'), $order_by);
    if (config('enable_force_active')) {
        $user_table_headers['force_active'] = Users_table_header_link('force_active', __('Forced'), $order_by);
    }
    if ($goodie_enabled) {
        if ($goodie_tshirt) {
            $user_table_headers['got_goodie'] = Users_table_header_link('got_goodie', __('T-Shirt'), $order_by);
            $user_table_headers['shirt_size'] = Users_table_header_link('shirt_size', __('Size'), $order_by);
        } else {
            $user_table_headers['got_goodie'] = Users_table_header_link('got_goodie', __('Goodie'), $order_by);
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

    $link = button(url('/register'), icon('plus-lg'), 'add');
    return page_with_title(__('All users') . ' ' . $link, [
        msg(),
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
        . url('/users', ['OrderBy' => $column])
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
        return '<span class="text-success">' . __('free') . '</span>';
    }

    /** @var ShiftEntry $nextShiftEntry */
    $nextShiftEntry = $upcoming_shifts->first();

    $start = $nextShiftEntry->shift->start;
    $end = $nextShiftEntry->shift->end;
    $startFormat = $start->format(__('general.datetime'));
    $endFormat = $end->format(__('general.datetime'));
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

    return '<span title="' . $end->format(__('general.datetime')) . '" data-countdown-ts="' . $end->timestamp . '">'
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
        . url('/angeltypes', ['action' => 'view', 'angeltype_id' => $needed_angel_type['id']])
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
    $nightShiftsConfig = config('night_shifts');
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;
    $supporter = auth()->user()->isAngelTypeSupporter(AngelType::findOrFail($shift->angel_type_id));

    $shift_info = '<a href="' . shift_link($shift) . '">' . htmlspecialchars($shift->shiftType->name) . '</a>';
    if ($shift->title) {
        $shift_info .= '<br /><a href="' . shift_link($shift) . '">' . htmlspecialchars($shift->title) . '</a>';
    }
    foreach ($shift->needed_angeltypes as $needed_angel_type) {
        $shift_info .= User_view_shiftentries($needed_angel_type);
    }

    $night_shift = '';
    if ($shift->isNightShift() && $goodie_enabled) {
        $night_shift = ' <span class="bi bi-moon-stars text-info" data-bs-toggle="tooltip" title="'
            . __('Night shifts between %d and %d am are multiplied by %d for the %s score.', [
                $nightShiftsConfig['start'],
                $nightShiftsConfig['end'],
                $nightShiftsConfig['multiplier'],
                ($goodie_tshirt ? __('T-shirt') : __('goodie')),
            ])
            . '"></span>';
    }
    $myshift = [
        'date'       => icon('calendar-event')
            . $shift->start->format(__('general.date')) . '<br>'
            . icon('clock-history') . $shift->start->format('H:i')
            . ' - '
            . $shift->end->format(__('H:i')),
        'duration'   => sprintf('%.2f', ($shift->end->timestamp - $shift->start->timestamp) / 3600) . '&nbsp;h',
        'hints'      => $night_shift,
        'location'   => location_name_render($shift->location),
        'shift_info' => $shift_info,
        'comment'    => '',
        'start'      => $shift->start,
        'end'        => $shift->end,
        'freeloaded' => $shift->freeloaded,
    ];

    if ($its_me) {
        $myshift['comment'] = htmlspecialchars($shift->user_comment);
    }

    if ($shift->freeloaded) {
        $myshift['duration'] = '<p class="text-danger"><s>'
            . sprintf('%.2f', ($shift->end->timestamp - $shift->start->timestamp) / 3600) . '&nbsp;h'
            . '</s></p>';
        if (auth()->can('user_shifts_admin') || $supporter) {
            $myshift['comment'] .= '<br />'
                . '<p class="text-danger">'
                . __('Freeloaded') . ': ' . htmlspecialchars($shift->freeloaded_comment)
                . '</p>';
        } else {
            $myshift['comment'] .= '<br /><p class="text-danger">'
                . __('Freeloaded')
                . '</p>';
        }
        if (!$goodie_enabled) {
            $freeload_info = __('freeload.info');
        } else {
            $freeload_info = __('freeload.info.goodie', [($goodie_tshirt
                ? __('T-shirt score')
                : __('Goodie score'))]);
        }
        $myshift['hints'] .= ' <span class="bi bi-info-circle-fill text-danger" data-bs-toggle="tooltip" title="'
            . $freeload_info
            . '"></span>';
    }

    $myshift['actions'] = [
        button(shift_link($shift), icon('eye'), 'btn-sm btn-info', '', __('View')),
    ];
    if ($its_me || auth()->can('user_shifts_admin') || $supporter) {
        $myshift['actions'][] = button(
            url('/user-myshifts', ['edit' => $shift->shift_entry_id, 'id' => $user_source->id]),
            icon('pencil'),
            'btn-sm',
            '',
            __('form.edit')
        );
    }

    if (Shift_signout_allowed($shift, (new AngelType())->forceFill(['id' => $shift->angel_type_id]), $user_source->id)) {
        $myshift['actions'][] = button(
            shift_entry_delete_link($shift),
            icon('trash'),
            'btn-sm btn-danger',
            '',
            __('Sign off')
        );
    }
    $myshift['actions'] = '<div class="text-end">' . table_buttons($myshift['actions']) . '</div>';

    return $myshift;
}

/**
 * Helper that prepares the shift table for user view
 *
 * @param Shift[]|Collection   $shifts
 * @param User                 $user_source
 * @param bool                 $its_me
 * @param string               $goodie_score
 * @param bool                 $goodie_admin
 * @param Worklog[]|Collection $user_worklogs
 * @param bool                 $admin_user_worklog_privilege
 *
 * @return array
 */
function User_view_myshifts(
    $shifts,
    $user_source,
    $its_me,
    $goodie_score,
    $goodie_admin,
    $user_worklogs,
    $admin_user_worklog_privilege
) {
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;
    $supported_angeltypes = auth()->user()
        ->userAngelTypes()
        ->where('supporter', true)
        ->pluck('angel_types.id');
    $show_sum = true;

    $myshifts_table = [];
    $timeSum = 0;
    foreach ($shifts as $shift) {
        $key = $shift->start->timestamp . '-shift-' . $shift->shift_entry_id . $shift->id;
        $supporter = $supported_angeltypes->contains($shift->angel_type_id);
        if (!auth()->can('user_shifts_admin') && !$supporter && !$its_me) {
            $show_sum = false;
            continue;
        }
        $myshifts_table[$key] = User_view_myshift($shift, $user_source, $its_me);
        if (!$shift->freeloaded) {
            $timeSum += ($shift->end->timestamp - $shift->start->timestamp);
        }
    }

    foreach ($user_worklogs as $worklog) {
        $key = $worklog->worked_at->timestamp . '-worklog-' . $worklog->id;
        $myshifts_table[$key] = User_view_worklog($worklog, $admin_user_worklog_privilege, $its_me);
        $timeSum += $worklog->hours * 3600;
    }

    if (count($myshifts_table) > 0) {
        ksort($myshifts_table);
        $myshifts_table = array_values($myshifts_table);
        foreach ($myshifts_table as $i => &$shift) {
            $before = $myshifts_table[$i - 1] ?? null;
            $after = $myshifts_table[$i + 1] ?? null;
            if ($shift['freeloaded']) {
                $shift['row-class'] = 'border border-danger border-2';
            } elseif (Carbon::now() > $shift['start'] &&  Carbon::now() < $shift['end']) {
                $shift['row-class'] = 'border border-info border-2';
            } elseif ($after && Carbon::now() > $shift['end'] && Carbon::now() < $after['start']) {
                $shift['row-class'] = 'border-bottom border-info';
            } elseif (!$before && Carbon::now() < $shift['start']) {
                $shift['row-class'] = 'border-top-info';
            } elseif (!$after && Carbon::now() > $shift['end']) {
                $shift['row-class'] = 'border-bottom border-info';
            }
        }
        if ($show_sum) {
            $myshifts_table[] = [
                'date'       => '<b>' . __('Sum:') . '</b>',
                'duration'   => '<b>' . sprintf('%.2f', round($timeSum / 3600, 2)) . '&nbsp;h</b>',
                'hints'      => '',
                'location'   => '',
                'shift_info' => '',
                'comment'    => '',
                'actions'    => '',
            ];
        }
        if ($goodie_enabled && ($its_me || $goodie_admin || auth()->can('admin_user'))) {
            $myshifts_table[] = [
                'date'       => '<b>' . ($goodie_tshirt ? __('T-shirt score') : __('Goodie score')) . '&trade;:</b>',
                'duration'   => '<b>' . $goodie_score . '</b>',
                'hints'      => '',
                'location'   => '',
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
function User_view_worklog(Worklog $worklog, $admin_user_worklog_privilege, $its_me)
{
    $actions = '';
    $self_worklog = config('enable_self_worklog') || !$its_me;

    if ($admin_user_worklog_privilege && $self_worklog) {
        $actions = '<div class="text-end">' . table_buttons([
            button(
                url('/admin/user/' . $worklog->user->id . '/worklog/' . $worklog->id),
                icon('pencil'),
                'btn-sm',
                '',
                __('form.edit')
            ),
            button(
                url('/admin/user/' . $worklog->user->id . '/worklog/' . $worklog->id . '/delete'),
                icon('trash'),
                'btn-sm btn-danger',
                '',
                __('form.delete')
            ),
        ]) . '</div>';
    }

    return [
        'date'       => icon('calendar-event') . date(__('general.date'), $worklog->worked_at->timestamp),
        'duration'   => sprintf('%.2f', $worklog->hours) . ' h',
        'hints'      => '',
        'location'   => '',
        'shift_info' => __('Work log entry'),
        'comment'    => htmlspecialchars($worklog->comment) . '<br>'
            . sprintf(
                __('Added by %s at %s'),
                User_Nick_render($worklog->creator),
                $worklog->created_at->format(__('general.datetime'))
            ),
        'actions'    => $actions,
        'start'      => $worklog->worked_at,
        'end'        => $worklog->worked_at,
        'freeloaded' => false,
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
 * @param string               $goodie_score
 * @param bool                 $goodie_admin
 * @param bool                 $admin_user_worklog_privilege
 * @param Worklog[]|Collection $user_worklogs
 * @param bool                 $admin_certificates
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
    $goodie_score,
    $goodie_admin,
    $admin_user_worklog_privilege,
    $user_worklogs,
    $admin_certificates
) {
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;
    $auth = auth();
    $nightShiftsConfig = config('night_shifts');
    $user_name = htmlspecialchars((string) $user_source->personalData->first_name) . ' '
        . htmlspecialchars((string) $user_source->personalData->last_name);
    $myshifts_table = '';
    $user_angeltypes_supporter = false;
    foreach ($user_source->userAngelTypes as $user_angeltype) {
        $user_angeltypes_supporter = $user_angeltypes_supporter
            || $auth->user()->isAngelTypeSupporter($user_angeltype);
    }

    if ($its_me || $admin_user_privilege || $goodie_admin || $user_angeltypes_supporter) {
        $my_shifts = User_view_myshifts(
            $shifts,
            $user_source,
            $its_me,
            $goodie_score,
            $goodie_admin,
            $user_worklogs,
            $admin_user_worklog_privilege
        );
        if (count($my_shifts) > 0) {
            $myshifts_table = div('table-responsive', table([
                'date'       => __('Day & Time'),
                'duration'   => __('Duration'),
                'hints'      => '',
                'location'   => __('Location'),
                'shift_info' => __('Name & Workmates'),
                'comment'    => __('worklog.comment'),
                'actions'    => __('general.actions'),
            ], $my_shifts));
        } elseif ($user_source->state->force_active && config('enable_force_active')) {
            $myshifts_table = success(
                ($its_me ? __('You have done enough.') : (__('%s has done enough.', [$user_source->name]))),
                true
            );
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

    $self_worklog = config('enable_self_worklog') || !$its_me;

    return page_with_title(
        '<span class="icon-icon_angel"></span> '
        . htmlspecialchars($user_source->name)
        . (config('enable_full_name') ? ' <small>' . $user_name . '</small>' : '')
        . ((config('enable_pronoun') && $user_source->personalData->pronoun)
            ? ' <small>(' . htmlspecialchars($user_source->personalData->pronoun) . ')</small> '
            : '')
        . user_info_icon($user_source),
        [
            msg(),
            div('row', [
                div('col-md-12', [
                    table_buttons([
                        $auth->can('user.goodie.edit') && $goodie_enabled ? button(
                            url('/admin/user/' . $user_source->id . '/goodie'),
                            icon('person') . ($goodie_tshirt ? __('T-shirt') : __('Goodie'))
                        ) : '',
                        $admin_user_privilege ? button(
                            url('/admin-user', ['id' => $user_source->id]),
                            icon('pencil') . __('form.edit'),
                        ) : '',
                        (($admin_user_privilege || $auth->can('admin_arrive')) && !$user_source->state->arrived) ?
                            form([
                                form_hidden('action', 'arrived'),
                                form_hidden('user', $user_source->id),
                                form_submit('submit', icon('house') . __('user.arrive'), '', false),
                            ], url('/admin-arrive'), 'float:left') : '',
                        ($admin_user_privilege || $auth->can('voucher.edit')) && config('enable_voucher') ?
                            button(
                                url(
                                    '/users',
                                    ['action' => 'edit_vouchers', 'user_id' => $user_source->id]
                                ),
                                icon('valentine') . __('Vouchers')
                            )
                            : '',
                        (
                            $admin_certificates
                            && (config('ifsg_enabled') || config('driving_license_enabled'))
                        ) ? button(
                            url('/users/' . $user_source->id . '/certificates'),
                            icon('card-checklist') . __('settings.certificates')
                        ) : '',
                        ($admin_user_worklog_privilege && $self_worklog) ? button(
                            url('/admin/user/' . $user_source->id . '/worklog'),
                            icon('clock-history') . __('worklog.add')
                        ) : '',
                    ], 'mb-2'),
                    $its_me ? table_buttons([
                        button(
                            url('/settings/profile'),
                            icon('person-fill-gear') . __('settings.settings')
                        ),
                        $auth->can('ical') ? button(
                            url('/ical', ['key' => $user_source->api_key]),
                            icon('calendar-week') . __('iCal Export')
                        ) : '',
                        $auth->can('shifts_json_export') ? button(
                            url('/shifts-json-export', ['key' => $user_source->api_key]),
                            icon('braces') . __('JSON Export')
                        ) : '',
                        $auth->canAny(['api', 'shifts_json_export', 'ical', 'atom']) ? button(
                            url('/settings/api'),
                            icon('arrow-repeat') . __('API Settings')
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
                            '<a href="' . url('/messages/' . $user_source->id) . '">'
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
            ($its_me || $admin_user_privilege) ? '<h2>' . __('general.shifts') . '</h2>' : '',
            $myshifts_table,
            ($its_me && $nightShiftsConfig['enabled'] && $goodie_enabled) ? info(
                sprintf(
                    icon('moon-stars') . __('Night shifts between %d and %d am are multiplied by %d for the %s score.', [
                        $nightShiftsConfig['start'],
                        $nightShiftsConfig['end'],
                        $nightShiftsConfig['multiplier'],
                        ($goodie_tshirt ? __('T-shirt') : __('goodie'))])
                ),
                true,
                true
            ) : '',
            $its_me && count($shifts) == 0
                ? error(sprintf(
                    __('Go to the <a href="%s">shifts table</a> to sign yourself up for some shifts.'),
                    url('/user-shifts')
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
        heading(__('State'), 4),
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
        $state[] = '<span class="text-success">' . icon('house') . __('user.arrived') . '</span>';
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
    $password_reset = PasswordReset::whereUserId($user_source->id)
        ->where('created_at', '>', $user_source->last_login_at ?: '')
        ->count();

    if ($freeloader) {
        $state[] = '<span class="text-danger">' . icon('exclamation-circle') . __('Freeloader') . '</span>';
    }

    $state[] = User_shift_state_render($user_source);

    if ($user_source->state->arrived) {
        $state[] = '<span class="text-success">' . icon('house')
            . sprintf(
                __('Arrived at %s'),
                $user_source->state->arrival_date ? $user_source->state->arrival_date->format(__('general.date')) : ''
            )
            . '</span>';

        if ($user_source->state->force_active && config('enable_force_active')) {
            $state[] = '<span class="text-success">' . __('user.force_active') . '</span>';
        } elseif ($user_source->state->active) {
            $state[] = '<span class="text-success">' . __('user.active') . '</span>';
        }
        if ($user_source->state->got_goodie && $goodie_enabled) {
            $state[] = '<span class="text-success">' . ($goodie_tshirt ? __('T-shirt') : __('Goodie')) . '</span>';
        }
    } else {
        $arrivalDate = $user_source->personalData->planned_arrival_date;
        $state[] = '<span class="text-danger">'
            . ($arrivalDate ? sprintf(
                __('Not arrived (Planned: %s)'),
                $arrivalDate->format(__('general.date'))
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

    if ($password_reset) {
        $state[] = __('Password reset in progress');
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
        heading(__('angeltypes.angeltypes'), 4),
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
    $profile_link = url('/settings/profile');
    if (!is_null($user_id)) {
        $profile_link = url('/users', ['action' => 'view', 'user_id' => $user_id]);
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
            __('freeload.freeloader.info'),
            config('max_freeloadable_shifts')
        );
    }

    return null;
}

/**
 * hint for angels, which are not arrived yet
 *
 * @return string|null
 */
function render_user_arrived_hint(bool $is_sys_menu = false)
{
    $user = auth()->user();
    $user_arrival_date = $user->personalData->planned_arrival_date;
    $is_before_arrival_date = $is_sys_menu && $user_arrival_date && Carbon::now() < $user_arrival_date;
    if (config('signup_requires_arrival') && !$user->state->arrived && !$is_before_arrival_date) {
        /** @var Carbon $buildup */
        $buildup = config('buildup_start');
        if (!empty($buildup) && $buildup->lessThan(new Carbon())) {
            return $user->state->user_info
                ? ($is_sys_menu ? null : __('user_info.not_arrived_hint'))
                : __('You are not marked as arrived. Please go to heaven, get your angel badge and/or tell them that you arrived already.');
        }
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_goodie_hint()
{
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_tshirt = $goodie === GoodieType::Tshirt;
    if (
        $goodie_tshirt
        && config('required_user_fields')['tshirt_size']
        && !auth()->user()->personalData->shirt_size
    ) {
        $text = __('tshirt.required.hint');
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
        (config('required_user_fields')['dect'] || $user->state->arrived)
        && config('enable_dect') && !$user->contact->dect
    ) {
        $text = __('dect.required.hint');
        return render_profile_link($text);
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_pronoun_hint()
{
    $user = auth()->user();
    if (config('required_user_fields')['pronoun'] && config('enable_pronoun') && !$user->personalData->pronoun) {
        $text = __('pronoun.required.hint');
        return render_profile_link($text);
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_firstname_hint()
{
    $user = auth()->user();
    if (config('required_user_fields')['firstname'] && config('enable_full_name') && !$user->personalData->first_name) {
        $text = __('firstname.required.hint');
        return render_profile_link($text);
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_lastname_hint()
{
    $user = auth()->user();
    if (config('required_user_fields')['lastname'] && config('enable_full_name') && !$user->personalData->last_name) {
        $text = __('lastname.required.hint');
        return render_profile_link($text);
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_mobile_hint()
{
    $user = auth()->user();
    if (config('required_user_fields')['mobile'] && !$user->contact->mobile) {
        $text = __('mobile.required.hint');
        return render_profile_link($text);
    }

    return null;
}
