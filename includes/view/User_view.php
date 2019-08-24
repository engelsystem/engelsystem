<?php

use Carbon\Carbon;
use Engelsystem\Models\User\User;

/**
 * Renders user settings page
 *
 * @param User  $user_source        The user
 * @param array $locales            Available languages
 * @param array $themes             Available themes
 * @param int   $buildup_start_date Unix timestamp
 * @param int   $teardown_end_date  Unix timestamp
 * @param bool  $enable_tshirt_size
 * @param array $tshirt_sizes
 * @return string
 */
function User_settings_view(
    $user_source,
    $locales,
    $themes,
    $buildup_start_date,
    $teardown_end_date,
    $enable_tshirt_size,
    $tshirt_sizes
) {
    $personalData = $user_source->personalData;
    $enable_user_name = config('enable_user_name');
    $enable_dect = config('enable_dect');
    $enable_planned_arrival = config('enable_planned_arrival');

    return page_with_title(settings_title(), [
        msg(),
        div('row', [
            div('col-md-6', [
                form([
                    form_info('', __('Here you can change your user details.')),
                    form_info(entry_required() . ' = ' . __('Entry required!')),
                    form_text('nick', __('Nick'), $user_source->name, true),
                    form_info(
                        '',
                        __('Use up to 23 letters, numbers, connecting punctuations or spaces for your nickname.')
                    ),
                    $enable_user_name ? form_text('lastname', __('Last name'), $personalData->last_name) : '',
                    $enable_user_name ? form_text('prename', __('First name'), $personalData->first_name) : '',
                    $enable_planned_arrival ? form_date(
                        'planned_arrival_date',
                        __('Planned date of arrival') . ' ' . entry_required(),
                        $personalData->planned_arrival_date ? $personalData->planned_arrival_date->getTimestamp() : '',
                        $buildup_start_date,
                        $teardown_end_date
                    ) : '',
                    $enable_planned_arrival ? form_date(
                        'planned_departure_date',
                        __('Planned date of departure'),
                        $personalData->planned_departure_date ? $personalData->planned_departure_date->getTimestamp() : '',
                        $buildup_start_date,
                        $teardown_end_date
                    ) : '',
                    $enable_dect ? form_text('dect', __('DECT'), $user_source->contact->dect) : '',
                    form_text('mobile', __('Mobile'), $user_source->contact->mobile),
                    form_text('mail', __('E-Mail') . ' ' . entry_required(), $user_source->email),
                    form_checkbox(
                        'email_shiftinfo',
                        __(
                            'The %s is allowed to send me an email (e.g. when my shifts change)',
                            [config('app_name')]
                        ),
                        $user_source->settings->email_shiftinfo
                    ),
                    form_checkbox(
                        'email_by_human_allowed',
                        __('Humans are allowed to send me an email (e.g. for ticket vouchers)'),
                        $user_source->settings->email_human
                    ),
                    $enable_tshirt_size ? form_select(
                        'tshirt_size',
                        __('Shirt size'),
                        $tshirt_sizes,
                        $personalData->shirt_size,
                        __('Please select...')
                    ) : '',
                    form_info('', __('Please visit the angeltypes page to manage your angeltypes.')),
                    form_submit('submit', __('Save'))
                ])
            ]),
            div('col-md-6', [
                form([
                    form_info(__('Here you can change your password.')),
                    form_password('password', __('Old password:')),
                    form_password('new_password', __('New password:')),
                    form_password('new_password2', __('Password confirmation:')),
                    form_submit('submit_password', __('Save'))
                ]),
                form([
                    form_info(__('Here you can choose your color settings:')),
                    form_select('theme', __('Color settings:'), $themes, $user_source->settings->theme),
                    form_submit('submit_theme', __('Save'))
                ]),
                form([
                    form_info(__('Here you can choose your language:')),
                    form_select('language', __('Language:'), $locales, $user_source->settings->language),
                    form_submit('submit_language', __('Save'))
                ])
            ])
        ])
    ]);
}

/**
 * Displays the welcome message to the user and shows a login form.
 *
 * @param string $event_welcome_message
 * @return string
 */
function User_registration_success_view($event_welcome_message)
{
    $parsedown = new Parsedown();
    $event_welcome_message = $parsedown->text($event_welcome_message);

    return page_with_title(__('Registration successful'), [
        msg(),
        div('row', [
            div('col-md-4', [
                $event_welcome_message
            ]),
            div('col-md-4', [
                '<h2>' . __('Login') . '</h2>',
                form([
                    form_text('login', __('Nick'), ''),
                    form_password('password', __('Password')),
                    form_submit('submit', __('Login')),
                    buttons([
                        button(page_link_to('user_password_recovery'), __('I forgot my password'))
                    ]),
                    info(__('Please note: You have to activate cookies!'), true)
                ], page_link_to('login'))
            ]),
            div('col-md-4', [
                '<h2>' . __('What can I do?') . '</h2>',
                '<p>' . __('Please read about the jobs you can do to help us.') . '</p>',
                buttons([
                    button(page_link_to('angeltypes', ['action' => 'about']), __('Teams/Job description') . ' &raquo;')
                ])
            ])
        ])
    ]);
}

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
            button(user_edit_link($user->id), glyph('chevron-left') . __('back'))
        ]),
        error(
            __('Do you really want to delete the user including all his shifts and every other piece of his data?'),
            true
        ),
        form([
            form_password('password', __('Your password')),
            form_submit('submit', __('Delete'))
        ])
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
            button(user_link($user->id), glyph('chevron-left') . __('back'))
        ]),
        info(sprintf(
            __('Angel should receive at least  %d vouchers.'),
            User_get_eligable_voucher_count($user)
        ), true),
        form(
            [
                form_spinner('vouchers', __('Number of vouchers given out'), $user->state->got_voucher),
                form_submit('submit', __('Save'))
            ],
            page_link_to('users', ['action' => 'edit_vouchers', 'user_id' => $user->id])
        )
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
    $usersList = [];
    foreach ($users as $user) {
        $u = [];
        $u['name'] = User_Nick_render($user);
        $u['first_name'] = $user->personalData->first_name;
        $u['last_name'] = $user->personalData->last_name;
        $u['dect'] = $user->contact->dect;
        $u['arrived'] = glyph_bool($user->state->arrived);
        $u['got_voucher'] = $user->state->got_voucher;
        $u['freeloads'] = $user->getAttribute('freeloads');
        $u['active'] = glyph_bool($user->state->active);
        $u['force_active'] = glyph_bool($user->state->force_active);
        $u['got_shirt'] = glyph_bool($user->state->got_shirt);
        $u['shirt_size'] = $user->personalData->shirt_size;
        $u['arrival_date'] = $user->personalData->planned_arrival_date
            ? $user->personalData->planned_arrival_date->format(__('Y-m-d')) : '';
        $u['departure_date'] = $user->personalData->planned_departure_date
            ? $user->personalData->planned_departure_date->format(__('Y-m-d')) : '';
        $u['last_login_at'] = $user->last_login_at ? $user->last_login_at->format(__('m/d/Y h:i a')) : '';
        $u['actions'] = table_buttons([
            button_glyph(page_link_to('admin_user', ['id' => $user->id]), 'edit', 'btn-xs')
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
        'actions'      => '<strong>' . count($usersList) . '</strong>'
    ];

    $user_table_headers = [
        'name'           => Users_table_header_link('name', __('Nick'), $order_by)
    ];
    if(config('enable_user_name')) {
        $user_table_headers['first_name'] = Users_table_header_link('first_name', __('Prename'), $order_by);
        $user_table_headers['last_name'] = Users_table_header_link('last_name', __('Name'), $order_by);
    }
    if(config('enable_dect')) {
        $user_table_headers['dect'] = Users_table_header_link('dect', __('DECT'), $order_by);
    }
    $user_table_headers['arrived'] = Users_table_header_link('arrived', __('Arrived'), $order_by);
    $user_table_headers['got_voucher'] = Users_table_header_link('got_voucher', __('Voucher'), $order_by);
    $user_table_headers['freeloads'] = __('Freeloads');
    $user_table_headers['active'] = Users_table_header_link('active', __('Active'), $order_by);
    $user_table_headers['force_active'] = Users_table_header_link('force_active', __('Forced'), $order_by);
    $user_table_headers['got_shirt'] = Users_table_header_link('got_shirt', __('T-Shirt'), $order_by);
    $user_table_headers['shirt_size'] = Users_table_header_link('shirt_size', __('Size'), $order_by);
    $user_table_headers['arrival_date'] = Users_table_header_link('planned_arrival_date', __('Planned arrival'), $order_by);
    $user_table_headers['departure_date'] = Users_table_header_link('planned_departure_date', __('Planned departure'), $order_by);
    $user_table_headers['last_login_at'] = Users_table_header_link('last_login_at', __('Last login'), $order_by);
    $user_table_headers['actions'] = '';

    return page_with_title(__('All users'), [
        msg(),
        buttons([
            button(page_link_to('register'), glyph('plus') . __('New user'))
        ]),
        table($user_table_headers, $usersList)
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

    $upcoming_shifts = ShiftEntries_upcoming_for_user($user->id);
    if (empty($upcoming_shifts)) {
        return '<span class="text-success">' . __('Free') . '</span>';
    }

    $nextShift = array_shift($upcoming_shifts);

    if ($nextShift['start'] > time()) {
        if ($nextShift['start'] - time() > 3600) {
            return '<span class="text-success moment-countdown" data-timestamp="' . $nextShift['start'] . '">'
                . __('Next shift %c')
                . '</span>';
        }
        return '<span class="text-warning moment-countdown" data-timestamp="' . $nextShift['start'] . '">'
            . __('Next shift %c')
            . '</span>';
    }
    $halfway = ($nextShift['start'] + $nextShift['end']) / 2;

    if (time() < $halfway) {
        return '<span class="text-danger moment-countdown" data-timestamp="' . $nextShift['start'] . '">'
            . __('Shift started %c')
            . '</span>';
    }

    return '<span class="text-danger moment-countdown" data-timestamp="' . $nextShift['end'] . '">'
        . __('Shift ends %c')
        . '</span>';
}

function User_last_shift_render($user)
{
    if (!$user->state->arrived) {
        return '';
    }

    $last_shifts = ShiftEntries_finished_by_user($user->id);
    if (empty($last_shifts)) {
        return '';
    }

    $lastShift = array_shift($last_shifts);
    return '<span class="moment-countdown" data-timestamp="' . $lastShift['end'] . '">'
        . __('Shift ended %c')
        . '</span>';
}

/**
 * @param array $needed_angel_type
 * @return string
 */
function User_view_shiftentries($needed_angel_type)
{
    $shift_info = '<br><b>' . $needed_angel_type['name'] . ':</b> ';

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
 * @param array $shift
 * @param User  $user_source
 * @param bool  $its_me
 * @return array
 */
function User_view_myshift($shift, $user_source, $its_me)
{
    $shift_info = '<a href="' . shift_link($shift) . '">' . $shift['name'] . '</a>';
    if ($shift['title']) {
        $shift_info .= '<br /><a href="' . shift_link($shift) . '">' . $shift['title'] . '</a>';
    }
    foreach ($shift['needed_angeltypes'] as $needed_angel_type) {
        $shift_info .= User_view_shiftentries($needed_angel_type);
    }

    $myshift = [
        'date'       => glyph('calendar')
            . date('Y-m-d', $shift['start']) . '<br>'
            . glyph('time') . date('H:i', $shift['start'])
            . ' - '
            . date('H:i', $shift['end']),
        'duration'   => sprintf('%.2f', ($shift['end'] - $shift['start']) / 3600) . '&nbsp;h',
        'room'       => Room_name_render($shift),
        'shift_info' => $shift_info,
        'comment'    => ''
    ];

    if ($its_me) {
        $myshift['comment'] = $shift['Comment'];
    }

    if ($shift['freeloaded']) {
        $myshift['duration'] = '<p class="text-danger">'
            . sprintf('%.2f', -($shift['end'] - $shift['start']) / 3600 * 2) . '&nbsp;h'
            . '</p>';
        if (auth()->can('user_shifts_admin')) {
            $myshift['comment'] .= '<br />'
                . '<p class="text-danger">' . __('Freeloaded') . ': ' . $shift['freeload_comment'] . '</p>';
        } else {
            $myshift['comment'] .= '<br /><p class="text-danger">' . __('Freeloaded') . '</p>';
        }
    }

    $myshift['actions'] = [
        button(shift_link($shift), glyph('eye-open') . __('view'), 'btn-xs')
    ];
    if ($its_me || auth()->can('user_shifts_admin')) {
        $myshift['actions'][] = button(
            page_link_to('user_myshifts', ['edit' => $shift['id'], 'id' => $user_source->id]),
            glyph('edit') . __('edit'),
            'btn-xs'
        );
    }
    if (Shift_signout_allowed($shift, ['id' => $shift['TID']], $user_source->id)) {
        $myshift['actions'][] = button(
            shift_entry_delete_link($shift),
            glyph('trash') . __('sign off'),
            'btn-xs'
        );
    }
    $myshift['actions'] = table_buttons($myshift['actions']);

    return $myshift;
}

/**
 * Helper that prepares the shift table for user view
 *
 * @param array[] $shifts
 * @param User    $user_source
 * @param bool    $its_me
 * @param int     $tshirt_score
 * @param bool    $tshirt_admin
 * @param array[] $user_worklogs
 * @param bool    $admin_user_worklog_privilege
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
    $myshifts_table = [];
    $timeSum = 0;
    foreach ($shifts as $shift) {
        $key = $shift['start'] . '-shift-' . $shift['SID'];
        $myshifts_table[$key] = User_view_myshift($shift, $user_source, $its_me);

        if (!$shift['freeloaded']) {
            $timeSum += ($shift['end'] - $shift['start']);
        }
    }

    if ($its_me || $admin_user_worklog_privilege) {
        foreach ($user_worklogs as $worklog) {
            $key = $worklog['work_timestamp'] . '-worklog-' . $worklog['id'];
            $myshifts_table[$key] = User_view_worklog($worklog, $admin_user_worklog_privilege);
            $timeSum += $worklog['work_hours'] * 3600;
        }
    }

    if (count($myshifts_table) > 0) {
        ksort($myshifts_table);
        $myshifts_table[] = [
            'date'       => '<b>' . __('Sum:') . '</b>',
            'duration'   => '<b>' . sprintf('%.2f', round($timeSum / 3600, 2)) . '&nbsp;h</b>',
            'room'       => '',
            'shift_info' => '',
            'comment'    => '',
            'actions'    => ''
        ];
        if (config('enable_tshirt_size', false) && ($its_me || $tshirt_admin)) {
            $myshifts_table[] = [
                'date'       => '<b>' . __('Your t-shirt score') . '&trade;:</b>',
                'duration'   => '<b>' . $tshirt_score . '</b>',
                'room'       => '',
                'shift_info' => '',
                'comment'    => '',
                'actions'    => ''
            ];
        }
    }
    return $myshifts_table;
}

/**
 * Renders table entry for user work log
 *
 * @param array $worklog
 * @param bool  $admin_user_worklog_privilege
 * @return array
 */
function User_view_worklog($worklog, $admin_user_worklog_privilege)
{
    $actions = '';
    if ($admin_user_worklog_privilege) {
        $actions = table_buttons([
            button(
                user_worklog_edit_link($worklog),
                glyph('edit') . __('edit'),
                'btn-xs'
            ),
            button(
                user_worklog_delete_link($worklog),
                glyph('trash') . __('delete'),
                'btn-xs'
            )
        ]);
    }

    return [
        'date'       => glyph('calendar') . date('Y-m-d', $worklog['work_timestamp']),
        'duration'   => sprintf('%.2f', $worklog['work_hours']) . ' h',
        'room'       => '',
        'shift_info' => __('Work log entry'),
        'comment'    => $worklog['comment'] . '<br>'
            . sprintf(
                __('Added by %s at %s'),
                User_Nick_render(User::find($worklog['created_user_id'])),
                date('Y-m-d H:i', $worklog['created_timestamp'])
            ),
        'actions'    => $actions
    ];
}

/**
 * Renders view for a single user
 *
 * @param User    $user_source
 * @param bool    $admin_user_privilege
 * @param bool    $freeloader
 * @param array[] $user_angeltypes
 * @param array[] $user_groups
 * @param array[] $shifts
 * @param bool    $its_me
 * @param int     $tshirt_score
 * @param bool    $tshirt_admin
 * @param bool    $admin_user_worklog_privilege
 * @param array[] $user_worklogs
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
    $nightShiftsConfig = config('night_shifts');
    $user_name = htmlspecialchars(
            $user_source->personalData->first_name) . ' ' . htmlspecialchars($user_source->personalData->last_name
        );
    $myshifts_table = '';
    if ($its_me || $admin_user_privilege) {
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
            $myshifts_table = table([
                'date'       => __('Day &amp; time'),
                'duration'   => __('Duration'),
                'room'       => __('Location'),
                'shift_info' => __('Name &amp; workmates'),
                'comment'    => __('Comment'),
                'actions'    => __('Action')
            ], $my_shifts);
        } elseif ($user_source->state->force_active) {
            $myshifts_table = success(__('You have done enough to get a t-shirt.'), true);
        }
    }

    return page_with_title(
        '<span class="icon-icon_angel"></span> '
        . htmlspecialchars($user_source->name)
        . (config('enable_user_name') ? ' <small>' . $user_name . '</small>' : ''),
        [
            msg(),
            div('row space-top', [
                div('col-md-12', [
                    buttons([
                        $admin_user_privilege ? button(
                            page_link_to('admin_user', ['id' => $user_source->id]),
                            glyph('edit') . __('edit')
                        ) : '',
                        $admin_user_privilege ? button(
                            user_driver_license_edit_link($user_source),
                            glyph('road') . __('driving license')
                        ) : '',
                        ($admin_user_privilege && !$user_source->state->arrived) ?
                            form([
                                form_hidden('action', 'arrived'),
                                form_hidden('user', $user_source->id),
                                form_submit('submit', __('arrived'), '', false, 'default')
                            ], page_link_to('admin_arrive'), true) : '',
                        $admin_user_privilege ? button(
                            page_link_to(
                                'users',
                                ['action' => 'edit_vouchers', 'user_id' => $user_source->id]
                            ),
                            glyph('cutlery') . __('Edit vouchers')
                        ) : '',
                        $admin_user_worklog_privilege ? button(
                            user_worklog_add_link($user_source),
                            glyph('list') . __('Add work log')
                        ) : '',
                        $its_me ? button(
                            page_link_to('user_settings'),
                            glyph('list-alt') . __('Settings')
                        ) : '',
                        $its_me ? button(
                            page_link_to('ical', ['key' => $user_source->api_key]),
                            glyph('calendar') . __('iCal Export')
                        ) : '',
                        $its_me ? button(
                            page_link_to('shifts_json_export', ['key' => $user_source->api_key]),
                            glyph('export') . __('JSON Export')
                        ) : '',
                        $its_me ? button(
                            page_link_to('user_myshifts', ['reset' => 1]),
                            glyph('repeat') . __('Reset API key')
                        ) : ''
                    ])
                ])
            ]),
            div('row', [
                div('col-md-3', [
                    heading(glyph('phone') . $user_source->contact->dect, 1)
                ]),
                User_view_state($admin_user_privilege, $freeloader, $user_source),
                User_angeltypes_render($user_angeltypes),
                User_groups_render($user_groups)
            ]),
            ($its_me || $admin_user_privilege) ? '<h2>' . __('Shifts') . '</h2>' : '',
            $myshifts_table,
            ($its_me && $nightShiftsConfig['enabled']) ? info(
                glyph('info-sign') . sprintf(
                    __('Your night shifts between %d and %d am count twice.'),
                    $nightShiftsConfig['start'],
                    $nightShiftsConfig['end']
                ),
                true
            ) : '',
            $its_me && count($shifts) == 0
                ? error(sprintf(
                __('Go to the <a href="%s">shifts table</a> to sign yourself up for some shifts.'),
                page_link_to('user_shifts')
            ), true)
                : '',
            ical_hint()
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

    return div('col-md-3', [
        heading(__('User state'), 4),
        join('<br>', $state)
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
        User_shift_state_render($user_source)
    ];

    if ($user_source->state->arrived) {
        $state[] = '<span class="text-success">' . glyph('home') . __('Arrived') . '</span>';
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

    if ($freeloader) {
        $state[] = '<span class="text-danger">' . glyph('exclamation-sign') . __('Freeloader') . '</span>';
    }

    $state[] = User_shift_state_render($user_source);

    if ($user_source->state->arrived) {
        $state[] = '<span class="text-success">' . glyph('home')
            . sprintf(
                __('Arrived at %s'),
                $user_source->state->arrival_date ? $user_source->state->arrival_date->format('Y-m-d') : ''
            )
            . '</span>';

        if ($user_source->state->force_active) {
            $state[] = '<span class="text-success">' . __('Active (forced)') . '</span>';
        } elseif ($user_source->state->active) {
            $state[] = '<span class="text-success">' . __('Active') . '</span>';
        }
        if ($user_source->state->got_shirt) {
            $state[] = '<span class="text-success">' . __('T-Shirt') . '</span>';
        }
    } else {
        $arrivalDate = $user_source->personalData->planned_arrival_date;
        $state[] = '<span class="text-danger">'
            . sprintf(
                __('Not arrived (Planned: %s)'),
                $arrivalDate ? $arrivalDate->format('Y-m-d') : ''
            )
            . '</span>';
    }

    if ($user_source->state->got_voucher > 0) {
        $voucherCount = $user_source->state->got_voucher;
        $state[] = '<span class="text-success">'
            . glyph('cutlery')
            . _e('Got %s voucher', 'Got %s vouchers', $voucherCount, [$voucherCount])
            . '</span>';
    } else {
        $state[] = '<span class="text-danger">' . __('Got no vouchers') . '</span>';
    }

    return $state;
}

/**
 * View for password recovery step 1: E-Mail
 *
 * @return string
 */
function User_password_recovery_view()
{
    return page_with_title(user_password_recovery_title(), [
        msg(),
        __('We will send you an e-mail with a password recovery link. Please use the email address you used for registration.'),
        form([
            form_text('email', __('E-Mail'), ''),
            form_submit('submit', __('Recover'))
        ])
    ]);
}

/**
 * View for password recovery step 2: New password
 *
 * @return string
 */
function User_password_set_view()
{
    return page_with_title(user_password_recovery_title(), [
        msg(),
        __('Please enter a new password.'),
        form([
            form_password('password', __('Password')),
            form_password('password2', __('Confirm password')),
            form_submit('submit', __('Save'))
        ])
    ]);
}

/**
 * @param array[] $user_angeltypes
 * @return string
 */
function User_angeltypes_render($user_angeltypes)
{
    $output = [];
    foreach ($user_angeltypes as $angeltype) {
        $class = 'text-success';
        if ($angeltype['restricted'] == 1 && empty($angeltype['confirm_user_id'])) {
            $class = 'text-warning';
        }
        $output[] = '<a href="' . angeltype_link($angeltype['id']) . '" class="' . $class . '">'
            . ($angeltype['supporter'] ? glyph('education') : '') . $angeltype['name']
            . '</a>';
    }
    return div('col-md-3', [
        heading(__('Angeltypes'), 4),
        join('<br>', $output)
    ]);
}

/**
 * @param array[] $user_groups
 * @return string
 */
function User_groups_render($user_groups)
{
    $output = [];
    foreach ($user_groups as $group) {
        $groupName = preg_replace('/(^\d+-)/', '', $group['Name']);
        $output[] = __($groupName);
    }

    return div('col-md-3', [
        '<h4>' . __('Rights') . '</h4>',
        join('<br>', $output)
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
        return sprintf('%s (%u)', $user->name, $user->id);
    }

    return render_profile_link(
        '<span class="icon-icon_angel"></span> ' . htmlspecialchars($user->name) . '</a>',
        $user->id,
        ($user->state->arrived ? '' : 'text-muted')
    );
}

/**
 * @param string $text
 * @param int    $user_id
 * @param string $class
 * @return string
 */
function render_profile_link($text, $user_id = null, $class = '')
{
    $profile_link = page_link_to('user-settings');
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
        return render_profile_link($text, null, 'alert-link');
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_freeloader_hint()
{
    if (User_is_freeloader(auth()->user())) {
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
    if (!auth()->user()->state->arrived) {
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
    if (config('enable_tshirt_size') && !auth()->user()->personalData->shirt_size) {
        $text = __('You need to specify a tshirt size in your settings!');
        return render_profile_link($text, null, 'alert-link');
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_dect_hint()
{
    $user = auth()->user();
    if ($user->state->arrived && config('enable_dect') && !$user->contact->dect) {
        $text = __('You need to specify a DECT phone number in your settings! If you don\'t have a DECT phone, just enter \'-\'.');
        return render_profile_link($text, null, 'alert-link');
    }

    return null;
}
