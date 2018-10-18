<?php

use Carbon\Carbon;

/**
 * Renders user settings page
 *
 * @param array $user_source        The user
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
    return page_with_title(settings_title(), [
        msg(),
        div('row', [
            div('col-md-6', [
                form([
                    form_info('', __('Here you can change your user details.')),
                    form_info(entry_required() . ' = ' . __('Entry required!')),
                    form_text('nick', __('Nick'), $user_source['Nick'], true),
                    form_text('lastname', __('Last name'), $user_source['Name']),
                    form_text('prename', __('First name'), $user_source['Vorname']),
                    form_date(
                        'planned_arrival_date',
                        __('Planned date of arrival') . ' ' . entry_required(),
                        $user_source['planned_arrival_date'],
                        $buildup_start_date,
                        $teardown_end_date
                    ),
                    form_date(
                        'planned_departure_date',
                        __('Planned date of departure'),
                        $user_source['planned_departure_date'],
                        $buildup_start_date,
                        $teardown_end_date
                    ),
                    form_text('age', __('Age'), $user_source['Alter']),
                    form_text('tel', __('Phone'), $user_source['Telefon']),
                    form_text('dect', __('DECT'), $user_source['DECT']),
                    form_text('mobile', __('Mobile'), $user_source['Handy']),
                    form_text('mail', __('E-Mail') . ' ' . entry_required(), $user_source['email']),
                    form_checkbox(
                        'email_shiftinfo',
                        __(
                            'The %s is allowed to send me an email (e.g. when my shifts change)',
                            [config('app_name')]
                        ),
                        $user_source['email_shiftinfo']
                    ),
                    form_checkbox(
                        'email_by_human_allowed',
                        __('Humans are allowed to send me an email (e.g. for ticket vouchers)'),
                        $user_source['email_by_human_allowed']
                    ),
                    form_text('jabber', __('Jabber'), $user_source['jabber']),
                    form_text('hometown', __('Hometown'), $user_source['Hometown']),
                    $enable_tshirt_size ? form_select(
                        'tshirt_size',
                        __('Shirt size'),
                        $tshirt_sizes,
                        $user_source['Size'],
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
                    form_select('theme', __('Color settings:'), $themes, $user_source['color']),
                    form_submit('submit_theme', __('Save'))
                ]),
                form([
                    form_info(__('Here you can choose your language:')),
                    form_select('language', __('Language:'), $locales, $user_source['Sprache']),
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
                    form_text('nick', __('Nick'), ''),
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
 * @param array $user
 * @return string
 */
function User_delete_view($user)
{
    return page_with_title(sprintf(__('Delete %s'), User_Nick_render($user)), [
        msg(),
        buttons([
            button(user_edit_link($user), glyph('chevron-left') . __('back'))
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
 * @param array $user
 * @return string
 */
function User_edit_vouchers_view($user)
{
    return page_with_title(sprintf(__('%s\'s vouchers'), User_Nick_render($user)), [
        msg(),
        buttons([
            button(user_link($user), glyph('chevron-left') . __('back'))
        ]),
        info(sprintf(
            __('Angel should receive at least  %d vouchers.'),
            User_get_eligable_voucher_count($user)
        ), true),
        form(
            [
                form_spinner('vouchers', __('Number of vouchers given out'), $user['got_voucher']),
                form_submit('submit', __('Save'))
            ],
            page_link_to('users', ['action' => 'edit_vouchers', 'user_id' => $user['UID']])
        )
    ]);
}

/**
 * @param array[] $users
 * @param string  $order_by
 * @param int     $arrived_count
 * @param int     $active_count
 * @param int     $force_active_count
 * @param int     $freeloads_count
 * @param int     $tshirts_count
 * @param int     $voucher_count
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
    foreach ($users as &$user) {
        $user['Nick'] = User_Nick_render($user);
        $user['Gekommen'] = glyph_bool($user['Gekommen']);
        $user['Aktiv'] = glyph_bool($user['Aktiv']);
        $user['force_active'] = glyph_bool($user['force_active']);
        $user['Tshirt'] = glyph_bool($user['Tshirt']);
        $user['lastLogIn'] = date(__('m/d/Y h:i a'), $user['lastLogIn']);
        $user['actions'] = table_buttons([
            button_glyph(page_link_to('admin_user', ['id' => $user['UID']]), 'edit', 'btn-xs')
        ]);
    }
    $users[] = [
        'Nick'         => '<strong>' . __('Sum') . '</strong>',
        'Gekommen'     => $arrived_count,
        'got_voucher'  => $voucher_count,
        'Aktiv'        => $active_count,
        'force_active' => $force_active_count,
        'freeloads'    => $freeloads_count,
        'Tshirt'       => $tshirts_count,
        'actions'      => '<strong>' . count($users) . '</strong>'
    ];

    return page_with_title(__('All users'), [
        msg(),
        buttons([
            button(page_link_to('register'), glyph('plus') . __('New user'))
        ]),
        table([
            'Nick'         => Users_table_header_link('Nick', __('Nick'), $order_by),
            'Vorname'      => Users_table_header_link('Vorname', __('Prename'), $order_by),
            'Name'         => Users_table_header_link('Name', __('Name'), $order_by),
            'DECT'         => Users_table_header_link('DECT', __('DECT'), $order_by),
            'Gekommen'     => Users_table_header_link('Gekommen', __('Arrived'), $order_by),
            'got_voucher'  => Users_table_header_link('got_voucher', __('Voucher'), $order_by),
            'freeloads'    => __('Freeloads'),
            'Aktiv'        => Users_table_header_link('Aktiv', __('Active'), $order_by),
            'force_active' => Users_table_header_link('force_active', __('Forced'), $order_by),
            'Tshirt'       => Users_table_header_link('Tshirt', __('T-Shirt'), $order_by),
            'Size'         => Users_table_header_link('Size', __('Size'), $order_by),
            'lastLogIn'    => Users_table_header_link('lastLogIn', __('Last login'), $order_by),
            'actions'      => ''
        ], $users)
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
 * @param array $user
 * @return string|false
 */
function User_shift_state_render($user)
{
    if (!$user['Gekommen']) {
        return '';
    }

    $upcoming_shifts = ShiftEntries_upcoming_for_user($user);

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
 * @param array $user_source
 * @param bool  $its_me
 * @return array
 */
function User_view_myshift($shift, $user_source, $its_me)
{
    global $privileges;

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
            . round(-($shift['end'] - $shift['start']) / 3600 * 2, 2) . '&nbsp;h'
            . '</p>';
        if (in_array('user_shifts_admin', $privileges)) {
            $myshift['comment'] .= '<br />'
                . '<p class="text-danger">' . __('Freeloaded') . ': ' . $shift['freeload_comment'] . '</p>';
        } else {
            $myshift['comment'] .= '<br /><p class="text-danger">' . __('Freeloaded') . '</p>';
        }
    }

    $myshift['actions'] = [
        button(shift_link($shift), glyph('eye-open') . __('view'), 'btn-xs')
    ];
    if ($its_me || in_array('user_shifts_admin', $privileges)) {
        $myshift['actions'][] = button(
            page_link_to('user_myshifts', ['edit' => $shift['id'], 'id' => $user_source['UID']]),
            glyph('edit') . __('edit'),
            'btn-xs'
        );
    }
    if (Shift_signout_allowed($shift, ['id' => $shift['TID']], $user_source)) {
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
 * @param array   $user_source
 * @param bool    $its_me
 * @param int     $tshirt_score
 * @param bool    $tshirt_admin
 * @param array[] $user_worklogs
 * @param         $admin_user_worklog_privilege
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
    $timesum = 0;
    foreach ($shifts as $shift) {
        $myshifts_table[$shift['start']] = User_view_myshift($shift, $user_source, $its_me);

        if (!$shift['freeloaded']) {
            $timesum += ($shift['end'] - $shift['start']);
        }
    }

    if ($its_me || $admin_user_worklog_privilege) {
        foreach ($user_worklogs as $worklog) {
            $myshifts_table[$worklog['work_timestamp']] = User_view_worklog($worklog, $admin_user_worklog_privilege);
            $timesum += $worklog['work_hours'] * 3600;
        }
    }

    if (count($myshifts_table) > 0) {
        ksort($myshifts_table);
        $myshifts_table[] = [
            'date'       => '<b>' . __('Sum:') . '</b>',
            'duration'   => '<b>' . sprintf('%.2f', round($timesum / 3600, 2)) . '&nbsp;h</b>',
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
        'duration'   => '<b>' . sprintf('%.2f', $worklog['work_hours']) . '</b>',
        'room'       => '',
        'shift_info' => __('Work log entry'),
        'comment'    => $worklog['comment'] . '<br>'
            . sprintf(
                __('Added by %s at %s'),
                User_Nick_render(User($worklog['created_user_id'])),
                date('Y-m-d H:i', $worklog['created_timestamp'])
            ),
        'actions'    => $actions
    ];
}

/**
 * Renders view for a single user
 *
 * @param array   $user_source
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
    $user_name = htmlspecialchars($user_source['Vorname']) . ' ' . htmlspecialchars($user_source['Name']);
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
        } elseif ($user_source['force_active']) {
            $myshifts_table = success(__('You have done enough to get a t-shirt.'), true);
        }
    }

    return page_with_title(
        '<span class="icon-icon_angel"></span> '
        . htmlspecialchars($user_source['Nick'])
        . ' <small>' . $user_name . '</small>',
        [
            msg(),
            div('row space-top', [
                div('col-md-12', [
                    buttons([
                        $admin_user_privilege ? button(
                            page_link_to('admin_user', ['id' => $user_source['UID']]),
                            glyph('edit') . __('edit')
                        ) : '',
                        $admin_user_privilege ? button(
                            user_driver_license_edit_link($user_source),
                            glyph('road') . __('driving license')
                        ) : '',
                        ($admin_user_privilege && !$user_source['Gekommen']) ? button(
                            page_link_to('admin_arrive', ['arrived' => $user_source['UID']]),
                            __('arrived')
                        ) : '',
                        $admin_user_privilege ? button(
                            page_link_to(
                                'users',
                                ['action' => 'edit_vouchers', 'user_id' => $user_source['UID']]
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
                            page_link_to('ical', ['key' => $user_source['api_key']]),
                            glyph('calendar') . __('iCal Export')
                        ) : '',
                        $its_me ? button(
                            page_link_to('shifts_json_export', ['key' => $user_source['api_key']]),
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
                    heading(glyph('phone') . $user_source['DECT'], 1)
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
 * @param bool  $admin_user_privilege
 * @param bool  $freeloader
 * @param array $user_source
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
 * @param array $user_source
 * @return array
 */
function User_view_state_user($user_source)
{
    $state = [
        User_shift_state_render($user_source)
    ];

    if ($user_source['Gekommen']) {
        $state[] = '<span class="text-success">' . glyph('home') . __('Arrived') . '</span>';
    } else {
        $state[] = '<span class="text-danger">' . __('Not arrived') . '</span>';
    }

    return $state;
}


/**
 * Render the state section of user view for admins.
 *
 * @param bool  $freeloader
 * @param array $user_source
 * @return array
 */
function User_view_state_admin($freeloader, $user_source)
{
    $state = [];

    if ($freeloader) {
        $state[] = '<span class="text-danger">' . glyph('exclamation-sign') . __('Freeloader') . '</span>';
    }

    $state[] = User_shift_state_render($user_source);

    if ($user_source['Gekommen']) {
        $state[] = '<span class="text-success">' . glyph('home')
            . sprintf(__('Arrived at %s'), date('Y-m-d', $user_source['arrival_date']))
            . '</span>';

        if ($user_source['force_active']) {
            $state[] = '<span class="text-success">' . __('Active (forced)') . '</span>';
        } elseif ($user_source['Aktiv']) {
            $state[] = '<span class="text-success">' . __('Active') . '</span>';
        }
        if ($user_source['Tshirt']) {
            $state[] = '<span class="text-success">' . __('T-Shirt') . '</span>';
        }
    } else {
        $state[] = '<span class="text-danger">'
            . sprintf(__('Not arrived (Planned: %s)'), date('Y-m-d', $user_source['planned_arrival_date']))
            . '</span>';
    }

    if ($user_source['got_voucher'] > 0) {
        $state[] = '<span class="text-success">'
            . glyph('cutlery')
            . _e('Got %s voucher', 'Got %s vouchers', $user_source['got_voucher'], [$user_source['got_voucher']])
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
 * @param array $user_source
 * @return string
 */
function User_Nick_render($user_source)
{
    return render_profile_link(
        '<span class="icon-icon_angel"></span> ' . htmlspecialchars($user_source['Nick']) . '</a>',
        $user_source['UID'],
        ($user_source['Gekommen'] ? '' : 'text-muted')
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
    global $user;

    if (!isset($user['planned_departure_date']) || empty($user['planned_departure_date'])) {
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
    global $user;

    if (User_is_freeloader($user)) {
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
    global $user;

    if ($user['Gekommen'] == 0) {
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
    global $user;

    if (config('enable_tshirt_size') && $user['Size'] == '') {
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
    global $user;

    if ($user['Gekommen'] == 1 && $user['DECT'] == '') {
        $text = __('You need to specify a DECT phone number in your settings! If you don\'t have a DECT phone, just enter \'-\'.');
        return render_profile_link($text, null, 'alert-link');
    }

    return null;
}
