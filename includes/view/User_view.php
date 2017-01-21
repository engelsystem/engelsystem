<?php

/**
 * Available T-Shirt sizes
 */
$tshirt_sizes = [
    ''     => _('Please select...'),
    'S'    => 'S',
    'M'    => 'M',
    'L'    => 'L',
    'XL'   => 'XL',
    '2XL'  => '2XL',
    '3XL'  => '3XL',
    '4XL'  => '4XL',
    '5XL'  => '5XL',
    'S-G'  => 'S Girl',
    'M-G'  => 'M Girl',
    'L-G'  => 'L Girl',
    'XL-G' => 'XL Girl'
];

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
                    form_info('', _('Here you can change your user details.')),
                    form_info(entry_required() . ' = ' . _('Entry required!')),
                    form_text('nick', _('Nick'), $user_source['Nick'], true),
                    form_text('lastname', _('Last name'), $user_source['Name']),
                    form_text('prename', _('First name'), $user_source['Vorname']),
                    form_date(
                        'planned_arrival_date',
                        _('Planned date of arrival') . ' ' . entry_required(),
                        $user_source['planned_arrival_date'],
                        $buildup_start_date,
                        $teardown_end_date
                    ),
                    form_date(
                        'planned_departure_date',
                        _('Planned date of departure'),
                        $user_source['planned_departure_date'],
                        $buildup_start_date,
                        $teardown_end_date
                    ),
                    form_text('age', _('Age'), $user_source['Alter']),
                    form_text('tel', _('Phone'), $user_source['Telefon']),
                    form_text('dect', _('DECT'), $user_source['DECT']),
                    form_text('mobile', _('Mobile'), $user_source['Handy']),
                    form_text('mail', _('E-Mail') . ' ' . entry_required(), $user_source['email']),
                    form_checkbox(
                        'email_shiftinfo',
                        _('The engelsystem is allowed to send me an email (e.g. when my shifts change)'),
                        $user_source['email_shiftinfo']
                    ),
                    form_checkbox(
                        'email_by_human_allowed',
                        _('Humans are allowed to send me an email (e.g. for ticket vouchers)'),
                        $user_source['email_by_human_allowed']
                    ),
                    form_text('jabber', _('Jabber'), $user_source['jabber']),
                    form_text('hometown', _('Hometown'), $user_source['Hometown']),
                    $enable_tshirt_size ? form_select(
                        'tshirt_size',
                        _('Shirt size'),
                        $tshirt_sizes,
                        $user_source['Size']
                    ) : '',
                    form_info('', _('Please visit the angeltypes page to manage your angeltypes.')),
                    form_submit('submit', _('Save'))
                ])
            ]),
            div('col-md-6', [
                form([
                    form_info(_('Here you can change your password.')),
                    form_password('password', _('Old password:')),
                    form_password('new_password', _('New password:')),
                    form_password('new_password2', _('Password confirmation:')),
                    form_submit('submit_password', _('Save'))
                ]),
                form([
                    form_info(_('Here you can choose your color settings:')),
                    form_select('theme', _('Color settings:'), $themes, $user_source['color']),
                    form_submit('submit_theme', _('Save'))
                ]),
                form([
                    form_info(_('Here you can choose your language:')),
                    form_select('language', _('Language:'), $locales, $user_source['Sprache']),
                    form_submit('submit_language', _('Save'))
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
    return page_with_title(_('Registration successful'), [
        msg(),
        div('row', [
            div('col-md-4', [
                $event_welcome_message
            ]),
            div('col-md-4', [
                '<h2>' . _('Login') . '</h2>',
                form([
                    form_text('nick', _('Nick'), ''),
                    form_password('password', _('Password')),
                    form_submit('submit', _('Login')),
                    buttons([
                        button(page_link_to('user_password_recovery'), _('I forgot my password'))
                    ]),
                    info(_('Please note: You have to activate cookies!'), true)
                ], page_link_to('login'))
            ]),
            div('col-md-4', [
                '<h2>' . _('What can I do?') . '</h2>',
                '<p>' . _('Please read about the jobs you can do to help us.') . '</p>',
                buttons([
                    button(page_link_to('angeltypes') . '&action=about', _('Teams/Job description') . ' &raquo;')
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
    return page_with_title(sprintf(_('Delete %s'), User_Nick_render($user)), [
        msg(),
        buttons([
            button(user_edit_link($user), glyph('chevron-left') . _('back'))
        ]),
        error(
            _('Do you really want to delete the user including all his shifts and every other piece of his data?'),
            true
        ),
        form([
            form_password('password', _('Your password')),
            form_submit('submit', _('Delete'))
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
    return page_with_title(sprintf(_('%s\'s vouchers'), User_Nick_render($user)), [
        msg(),
        buttons([
            button(user_link($user), glyph('chevron-left') . _('back'))
        ]),
        info(sprintf(_('Angel should receive at least  %d vouchers.'), User_get_eligable_voucher_count($user)), true),
        form([
            form_spinner('vouchers', _('Number of vouchers given out'), $user['got_voucher']),
            form_submit('submit', _('Save'))
        ], page_link_to('users') . '&action=edit_vouchers&user_id=' . $user['UID'])
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
        $user['lastLogIn'] = date(_('m/d/Y h:i a'), $user['lastLogIn']);
        $user['actions'] = table_buttons([
            button_glyph(page_link_to('admin_user') . '&id=' . $user['UID'], 'edit', 'btn-xs')
        ]);
    }
    $users[] = [
        'Nick'         => '<strong>' . _('Sum') . '</strong>',
        'Gekommen'     => $arrived_count,
        'got_voucher'  => $voucher_count,
        'Aktiv'        => $active_count,
        'force_active' => $force_active_count,
        'freeloads'    => $freeloads_count,
        'Tshirt'       => $tshirts_count,
        'actions'      => '<strong>' . count($users) . '</strong>'
    ];

    return page_with_title(_('All users'), [
        msg(),
        buttons([
            button(page_link_to('register'), glyph('plus') . _('New user'))
        ]),
        table([
            'Nick'         => Users_table_header_link('Nick', _('Nick'), $order_by),
            'Vorname'      => Users_table_header_link('Vorname', _('Prename'), $order_by),
            'Name'         => Users_table_header_link('Name', _('Name'), $order_by),
            'DECT'         => Users_table_header_link('DECT', _('DECT'), $order_by),
            'Gekommen'     => Users_table_header_link('Gekommen', _('Arrived'), $order_by),
            'got_voucher'  => Users_table_header_link('got_voucher', _('Voucher'), $order_by),
            'freeloads'    => _('Freeloads'),
            'Aktiv'        => Users_table_header_link('Aktiv', _('Active'), $order_by),
            'force_active' => Users_table_header_link('force_active', _('Forced'), $order_by),
            'Tshirt'       => Users_table_header_link('Tshirt', _('T-Shirt'), $order_by),
            'Size'         => Users_table_header_link('Size', _('Size'), $order_by),
            'lastLogIn'    => Users_table_header_link('lastLogIn', _('Last login'), $order_by),
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
    return '<a href="' . page_link_to('users') . '&OrderBy=' . $column . '">' . $label . ($order_by == $column ? ' <span class="caret"></span>' : '') . '</a>';
}

/**
 * @param array $user
 * @return string|false
 */
function User_shift_state_render($user)
{
    $upcoming_shifts = ShiftEntries_upcoming_for_user($user);

    if (empty($upcoming_shifts)) {
        return '<span class="text-success">' . _('Free') . '</span>';
    }

    $nextShift = array_shift($upcoming_shifts);

    if ($nextShift['start'] > time()) {
        if ($nextShift['start'] - time() > 3600) {
            return '<span class="text-success moment-countdown" data-timestamp="' . $nextShift['start'] . '">' . _('Next shift %c') . '</span>';
        }
        return '<span class="text-warning moment-countdown" data-timestamp="' . $nextShift['start'] . '">' . _('Next shift %c') . '</span>';
    }
    $halfway = ($nextShift['start'] + $nextShift['end']) / 2;

    if (time() < $halfway) {
        return '<span class="text-danger moment-countdown" data-timestamp="' . $nextShift['start'] . '">' . _('Shift starts %c') . '</span>';
    }
    return '<span class="text-danger moment-countdown" data-timestamp="' . $nextShift['end'] . '">' . _('Shift ends %c') . '</span>';
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
    global $last_unsubscribe, $privileges;

    $shift_info = '<a href="' . shift_link($shift) . '">' . $shift['name'] . '</a>';
    if ($shift['title']) {
        $shift_info .= '<br /><a href="' . shift_link($shift) . '">' . $shift['title'] . '</a>';
    }
    foreach ($shift['needed_angeltypes'] as $needed_angel_type) {
        $shift_info .= User_view_shiftentries($needed_angel_type);
    }

    $myshift = [
        'date'       => date('Y-m-d', $shift['start']),
        'time'       => date('H:i', $shift['start']) . ' - ' . date('H:i', $shift['end']),
        'room'       => $shift['Name'],
        'shift_info' => $shift_info,
        'comment'    => $shift['Comment']
    ];

    if ($shift['freeloaded']) {
        if (in_array('user_shifts_admin', $privileges)) {
            $myshift['comment'] .= '<br /><p class="error">' . _('Freeloaded') . ': ' . $shift['freeload_comment'] . '</p>';
        } else {
            $myshift['comment'] .= '<br /><p class="error">' . _('Freeloaded') . '</p>';
        }
    }

    $myshift['actions'] = [
        button(shift_link($shift), glyph('eye-open') . _('view'), 'btn-xs')
    ];
    if ($its_me || in_array('user_shifts_admin', $privileges)) {
        $myshift['actions'][] = button(
            page_link_to('user_myshifts') . '&edit=' . $shift['id'] . '&id=' . $user_source['UID'],
            glyph('edit') . _('edit'),
            'btn-xs'
        );
    }
    if (($shift['start'] > time() + $last_unsubscribe * 3600) || in_array('user_shifts_admin', $privileges)) {
        $myshift['actions'][] = button(
            page_link_to('user_myshifts') . ((!$its_me) ? '&id=' . $user_source['UID'] : '') . '&cancel=' . $shift['id'],
            glyph('trash') . _('sign off'),
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
 * @return array
 */
function User_view_myshifts($shifts, $user_source, $its_me)
{
    $myshifts_table = [];
    $timesum = 0;
    foreach ($shifts as $shift) {
        $myshifts_table[] = User_view_myshift($shift, $user_source, $its_me);

        if ($shift['freeloaded']) {
            $timesum += (-2 * ($shift['end'] - $shift['start']));
        } else {
            $timesum += ($shift['end'] - $shift['start']);
        }
    }

    if (count($myshifts_table) > 0) {
        $myshifts_table[] = [
            'date'       => '<b>' . _('Sum:') . '</b>',
            'time'       => '<b>' . round($timesum / 3600, 1) . ' h</b>',
            'room'       => '',
            'shift_info' => '',
            'comment'    => '',
            'actions'    => ''
        ];
    }
    return $myshifts_table;
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
 * @return string
 */
function User_view($user_source, $admin_user_privilege, $freeloader, $user_angeltypes, $user_groups, $shifts, $its_me)
{
    $user_name = htmlspecialchars($user_source['Vorname']) . ' ' . htmlspecialchars($user_source['Name']);
    $myshifts_table = User_view_myshifts($shifts, $user_source, $its_me);

    return page_with_title(
        '<span class="icon-icon_angel"></span> ' . htmlspecialchars($user_source['Nick']) . ' <small>' . $user_name . '</small>',
        [
            msg(),
            div('row space-top', [
                div('col-md-12', [
                    buttons([
                        $admin_user_privilege ? button(
                            page_link_to('admin_user') . '&id=' . $user_source['UID'],
                            glyph('edit') . _('edit')
                        ) : '',
                        $admin_user_privilege ? button(
                            user_driver_license_edit_link($user_source),
                            glyph('road') . _('driving license')
                        ) : '',
                        ($admin_user_privilege && !$user_source['Gekommen']) ? button(
                            page_link_to('admin_arrive') . '&arrived=' . $user_source['UID'],
                            _('arrived')
                        ) : '',
                        $admin_user_privilege ? button(
                            page_link_to('users') . '&action=edit_vouchers&user_id=' . $user_source['UID'],
                            glyph('cutlery') . _('Edit vouchers')
                        ) : '',
                        $its_me ? button(page_link_to('user_settings'), glyph('list-alt') . _('Settings')) : '',
                        $its_me ? button(
                            page_link_to('ical') . '&key=' . $user_source['api_key'],
                            glyph('calendar') . _('iCal Export')
                        ) : '',
                        $its_me ? button(
                            page_link_to('shifts_json_export') . '&key=' . $user_source['api_key'],
                            glyph('export') . _('JSON Export')
                        ) : '',
                        $its_me ? button(
                            page_link_to('user_myshifts') . '&reset',
                            glyph('repeat') . _('Reset API key')
                        ) : ''
                    ])
                ])
            ]),
            div('row', [
                div('col-md-3', [
                    '<h1>',
                    '<span class="glyphicon glyphicon-phone"></span>',
                    $user_source['DECT'],
                    '</h1>'
                ]),
                div('col-md-3', [
                    '<h4>' . _('User state') . '</h4>',
                    ($admin_user_privilege && $freeloader)
                        ? '<span class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> ' . _('Freeloader') . '</span><br />'
                        : '',
                    $user_source['Gekommen']
                        ? User_shift_state_render($user_source) . '<br />'
                        : '',
                    $admin_user_privilege || $its_me
                        ? (
                    $user_source['Gekommen']
                        ? '<span class="text-success"><span class="glyphicon glyphicon-home"></span> '
                        . sprintf(_('Arrived at %s'), date('Y-m-d', $user_source['arrival_date']))
                        . '</span>'
                        : '<span class="text-danger">'
                        . sprintf(_('Not arrived (Planned: %s)'), date('Y-m-d', $user_source['planned_arrival_date']))
                        . '</span>'
                    )
                        : (
                    $user_source['Gekommen']
                        ? '<span class="text-success"><span class="glyphicon glyphicon-home"></span> ' . _('Arrived') . '</span>'
                        : '<span class="text-danger">' . _('Not arrived') . '</span>'),
                    $admin_user_privilege
                        ? (
                    $user_source['got_voucher'] > 0
                        ? '<br /><span class="text-success">'
                        . glyph('cutlery')
                        . sprintf(
                            ngettext('Got %s voucher', 'Got %s vouchers', $user_source['got_voucher']),
                            $user_source['got_voucher']
                        )
                        . '</span><br />'
                        : '<br /><span class="text-danger">' . _('Got no vouchers') . '</span><br />')
                        : '',
                    ($user_source['Gekommen'] && $admin_user_privilege && $user_source['Aktiv']) ? ' <span class="text-success">' . _('Active') . '</span>' : '',
                    ($user_source['Gekommen'] && $admin_user_privilege && $user_source['Tshirt']) ? ' <span class="text-success">' . _('T-Shirt') . '</span>' : ''
                ]),
                div('col-md-3', [
                    '<h4>' . _('Angeltypes') . '</h4>',
                    User_angeltypes_render($user_angeltypes)
                ]),
                div('col-md-3', [
                    '<h4>' . _('Rights') . '</h4>',
                    User_groups_render($user_groups)
                ])
            ]),
            ($its_me || $admin_user_privilege) ? '<h2>' . _('Shifts') . '</h2>' : '',
            ($its_me || $admin_user_privilege) ? table([
                'date'       => _('Day'),
                'time'       => _('Time'),
                'room'       => _('Location'),
                'shift_info' => _('Name &amp; workmates'),
                'comment'    => _('Comment'),
                'actions'    => _('Action')
            ], $myshifts_table) : '',
            $its_me ? info(glyph('info-sign') . _('Your night shifts between 2 and 8 am count twice.'), true) : '',
            $its_me && count($shifts) == 0
                ? error(sprintf(
                _('Go to the <a href="%s">shifts table</a> to sign yourself up for some shifts.'),
                page_link_to('user_shifts')
            ), true)
                : ''
        ]
    );
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
        _('We will send you an e-mail with a password recovery link. Please use the email address you used for registration.'),
        form([
            form_text('email', _('E-Mail'), ''),
            form_submit('submit', _('Recover'))
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
        _('Please enter a new password.'),
        form([
            form_password('password', _('Password')),
            form_password('password2', _('Confirm password')),
            form_submit('submit', _('Save'))
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
        if ($angeltype['restricted'] == 1 && $angeltype['confirm_user_id'] == null) {
            $class = 'text-warning';
        }
        $output[] = '<a href="' . angeltype_link($angeltype['id']) . '" class="' . $class . '">'
            . ($angeltype['supporter'] ? glyph('education') : '') . $angeltype['name']
            . '</a>';
    }
    return join('<br />', $output);
}

/**
 * @param array[] $user_groups
 * @return string
 */
function User_groups_render($user_groups)
{
    $output = [];
    foreach ($user_groups as $group) {
        $output[] = substr($group['Name'], 2);
    }
    return join('<br />', $output);
}

/**
 * Render a user nickname.
 *
 * @param array $user_source
 * @return string
 */
function User_Nick_render($user_source)
{
    return '<a class="' . ($user_source['Gekommen'] ? '' : 'text-muted') . '" href="'
        . page_link_to('users') . '&amp;action=view&amp;user_id=' . $user_source['UID']
        . '"><span class="icon-icon_angel"></span> ' . htmlspecialchars($user_source['Nick']) . '</a>';
}

/**
 * @return string|null
 */
function render_user_departure_date_hint()
{
    global $user;

    if (!isset($user['planned_departure_date']) || $user['planned_departure_date'] == null) {
        return _('Please enter your planned date of departure on your settings page to give us a feeling for teardown capacities.');
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_freeloader_hint()
{
    global $user, $max_freeloadable_shifts;

    if (User_is_freeloader($user)) {
        return sprintf(
            _('You freeloaded at least %s shifts. Shift signup is locked. Please go to heavens desk to be unlocked again.'),
            $max_freeloadable_shifts
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
        return _('You are not marked as arrived. Please go to heaven\'s desk, get your angel badge and/or tell them that you arrived already.');
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_tshirt_hint()
{
    global $enable_tshirt_size, $user;

    if ($enable_tshirt_size && $user['Size'] == '') {
        return _('You need to specify a tshirt size in your settings!');
    }

    return null;
}

/**
 * @return string|null
 */
function render_user_dect_hint()
{
    global $user;

    if ($user['DECT'] == '') {
        return _('You need to specify a DECT phone number in your settings! If you don\'t have a DECT phone, just enter \'-\'.');
    }

    return null;
}
