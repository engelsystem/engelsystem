<?php

use Engelsystem\Database\DB;

/**
 * @return string
 */
function login_title()
{
    return _('Login');
}

/**
 * @return string
 */
function register_title()
{
    return _('Register');
}

/**
 * @return string
 */
function logout_title()
{
    return _('Logout');
}

/**
 * Engel registrieren
 *
 * @return string
 */
function guest_register()
{
    global $user, $privileges;
    $tshirt_sizes = config('tshirt_sizes');
    $enable_tshirt_size = config('enable_tshirt_size');
    $min_password_length = config('min_password_length');
    $event_config = EventConfig();
    $request = request();
    $session = session();

    $msg = '';
    $nick = '';
    $lastName = '';
    $preName = '';
    $age = 0;
    $tel = '';
    $dect = '';
    $mobile = '';
    $mail = '';
    $email_shiftinfo = false;
    $email_by_human_allowed = false;
    $jabber = '';
    $hometown = '';
    $comment = '';
    $tshirt_size = '';
    $password_hash = '';
    $selected_angel_types = [];
    $planned_arrival_date = null;

    $angel_types_source = AngelTypes();
    $angel_types = [];
    foreach ($angel_types_source as $angel_type) {
        $angel_types[$angel_type['id']] = $angel_type['name'] . ($angel_type['restricted'] ? ' (restricted)' : '');
        if (!$angel_type['restricted']) {
            $selected_angel_types[] = $angel_type['id'];
        }
    }

    foreach ($tshirt_sizes as $key => $size) {
        if (empty($size)) {
            unset($tshirt_sizes[$key]);
        }
    }

    if (!in_array('register', $privileges) || (!isset($user) && !config('registration_enabled'))) {
        error(_('Registration is disabled.'));

        return page_with_title(register_title(), [
            msg(),
        ]);
    }

    if ($request->has('submit')) {
        $valid = true;

        if ($request->has('nick') && strlen(User_validate_Nick($request->input('nick'))) > 1) {
            $nick = User_validate_Nick($request->input('nick'));
            if (count(DB::select('SELECT `UID` FROM `User` WHERE `Nick`=? LIMIT 1', [$nick])) > 0) {
                $valid = false;
                $msg .= error(sprintf(_('Your nick &quot;%s&quot; already exists.'), $nick), true);
            }
        } else {
            $valid = false;
            $msg .= error(sprintf(
                _('Your nick &quot;%s&quot; is too short (min. 2 characters).'),
                User_validate_Nick($request->input('nick'))
            ), true);
        }

        if ($request->has('mail') && strlen(strip_request_item('mail')) > 0) {
            $mail = strip_request_item('mail');
            if (!check_email($mail)) {
                $valid = false;
                $msg .= error(_('E-mail address is not correct.'), true);
            }
        } else {
            $valid = false;
            $msg .= error(_('Please enter your e-mail.'), true);
        }

        if ($request->has('email_shiftinfo')) {
            $email_shiftinfo = true;
        }

        if ($request->has('email_by_human_allowed')) {
            $email_by_human_allowed = true;
        }

        if ($request->has('jabber') && strlen(strip_request_item('jabber')) > 0) {
            $jabber = strip_request_item('jabber');
            if (!check_email($jabber)) {
                $valid = false;
                $msg .= error(_('Please check your jabber account information.'), true);
            }
        }

        if ($enable_tshirt_size) {
            if ($request->has('tshirt_size') && isset($tshirt_sizes[$request->input('tshirt_size')])) {
                $tshirt_size = $request->input('tshirt_size');
            } else {
                $valid = false;
                $msg .= error(_('Please select your shirt size.'), true);
            }
        }

        if ($request->has('password') && strlen($request->postData('password')) >= $min_password_length) {
            if ($request->postData('password') != $request->postData('password2')) {
                $valid = false;
                $msg .= error(_('Your passwords don\'t match.'), true);
            }
        } else {
            $valid = false;
            $msg .= error(sprintf(
                _('Your password is too short (please use at least %s characters).'),
                $min_password_length
            ), true);
        }

        if ($request->has('planned_arrival_date')) {
            $tmp = parse_date('Y-m-d H:i', $request->input('planned_arrival_date') . ' 00:00');
            $result = User_validate_planned_arrival_date($tmp);
            $planned_arrival_date = $result->getValue();
            if (!$result->isValid()) {
                $valid = false;
                error(_('Please enter your planned date of arrival. It should be after the buildup start date and before teardown end date.'));
            }
        }

        $selected_angel_types = [];
        foreach (array_keys($angel_types) as $angel_type_id) {
            if ($request->has('angel_types_' . $angel_type_id)) {
                $selected_angel_types[] = $angel_type_id;
            }
        }

        // Trivia
        if ($request->has('lastname')) {
            $lastName = strip_request_item('lastname');
        }
        if ($request->has('prename')) {
            $preName = strip_request_item('prename');
        }
        if ($request->has('age') && preg_match('/^\d{0,4}$/', $request->input('age'))) {
            $age = strip_request_item('age');
        }
        if ($request->has('tel')) {
            $tel = strip_request_item('tel');
        }
        if ($request->has('dect')) {
            $dect = strip_request_item('dect');
        }
        if ($request->has('mobile')) {
            $mobile = strip_request_item('mobile');
        }
        if ($request->has('hometown')) {
            $hometown = strip_request_item('hometown');
        }
        if ($request->has('comment')) {
            $comment = strip_request_item_nl('comment');
        }

        if ($valid) {
            DB::insert('
                    INSERT INTO `User` (
                        `color`,
                        `Nick`,
                        `Vorname`,
                        `Name`,
                        `Alter`,
                        `Telefon`,
                        `DECT`,
                        `Handy`,
                        `email`,
                        `email_shiftinfo`,
                        `email_by_human_allowed`,
                        `jabber`,
                        `Size`,
                        `Passwort`,
                        `kommentar`,
                        `Hometown`,
                        `CreateDate`,
                        `Sprache`,
                        `arrival_date`,
                        `planned_arrival_date`,
                        `force_active`
                    )
                    VALUES  (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, NULL, ?, FALSE)
                ',
                [
                    config('theme'),
                    $nick,
                    $preName,
                    $lastName,
                    $age,
                    $tel,
                    $dect,
                    $mobile,
                    $mail,
                    (int)$email_shiftinfo,
                    (int)$email_by_human_allowed,
                    $jabber,
                    $tshirt_size,
                    $password_hash,
                    $comment,
                    $hometown,
                    $session->get('locale'),
                    $planned_arrival_date,
                ]
            );

            // Assign user-group and set password
            $user_id = DB::getPdo()->lastInsertId();
            DB::insert('INSERT INTO `UserGroups` (`uid`, `group_id`) VALUES (?, -20)', [$user_id]);
            set_password($user_id, $request->postData('password'));

            // Assign angel-types
            $user_angel_types_info = [];
            foreach ($selected_angel_types as $selected_angel_type_id) {
                DB::insert(
                    'INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`) VALUES (?, ?)',
                    [$user_id, $selected_angel_type_id]
                );
                $user_angel_types_info[] = $angel_types[$selected_angel_type_id];
            }

            engelsystem_log(
                'User ' . User_Nick_render(User($user_id))
                . ' signed up as: ' . join(', ', $user_angel_types_info)
            );
            success(_('Angel registration successful!'));

            // User is already logged in - that means a supporter has registered an angel. Return to register page.
            if (isset($user)) {
                redirect(page_link_to('register'));
            }

            // If a welcome message is present, display registration success page.
            if ($event_config != null && $event_config['event_welcome_msg'] != null) {
                return User_registration_success_view($event_config['event_welcome_msg']);
            }

            redirect(page_link_to('/'));
        }
    }

    $buildup_start_date = time();
    $teardown_end_date = null;
    if ($event_config != null) {
        if (isset($event_config['buildup_start_date'])) {
            $buildup_start_date = $event_config['buildup_start_date'];
        }
        if (isset($event_config['teardown_end_date'])) {
            $teardown_end_date = $event_config['teardown_end_date'];
        }
    }

    return page_with_title(register_title(), [
        _('By completing this form you\'re registering as a Chaos-Angel. This script will create you an account in the angel task scheduler.'),
        $msg,
        msg(),
        form([
            div('row', [
                div('col-md-6', [
                    div('row', [
                        div('col-sm-4', [
                            form_text('nick', _('Nick') . ' ' . entry_required(), $nick)
                        ]),
                        div('col-sm-8', [
                            form_email('mail', _('E-Mail') . ' ' . entry_required(), $mail),
                            form_checkbox(
                                'email_shiftinfo',
                                _('The engelsystem is allowed to send me an email (e.g. when my shifts change)'),
                                $email_shiftinfo
                            ),
                            form_checkbox(
                                'email_by_human_allowed',
                                _('Humans are allowed to send me an email (e.g. for ticket vouchers)'),
                                $email_by_human_allowed
                            )
                        ])
                    ]),
                    div('row', [
                        div('col-sm-6', [
                            form_date(
                                'planned_arrival_date',
                                _('Planned date of arrival') . ' ' . entry_required(),
                                $planned_arrival_date, $buildup_start_date, $teardown_end_date
                            )
                        ]),
                        div('col-sm-6', [
                            $enable_tshirt_size ? form_select('tshirt_size',
                                _('Shirt size') . ' ' . entry_required(),
                                $tshirt_sizes, $tshirt_size) : ''
                        ])
                    ]),
                    div('row', [
                        div('col-sm-6', [
                            form_password('password', _('Password') . ' ' . entry_required())
                        ]),
                        div('col-sm-6', [
                            form_password('password2', _('Confirm password') . ' ' . entry_required())
                        ])
                    ]),
                    form_checkboxes(
                        'angel_types',
                        _('What do you want to do?') . sprintf(
                            ' (<a href="%s">%s</a>)',
                            page_link_to('angeltypes', ['action' => 'about']),
                            _('Description of job types')
                        ),
                        $angel_types,
                        $selected_angel_types
                    ),
                    form_info(
                        '',
                        _('Restricted angel types need will be confirmed later by a supporter. You can change your selection in the options section.')
                    )
                ]),
                div('col-md-6', [
                    div('row', [
                        div('col-sm-4', [
                            form_text('dect', _('DECT'), $dect)
                        ]),
                        div('col-sm-4', [
                            form_text('mobile', _('Mobile'), $mobile)
                        ]),
                        div('col-sm-4', [
                            form_text('tel', _('Phone'), $tel)
                        ])
                    ]),
                    form_text('jabber', _('Jabber'), $jabber),
                    div('row', [
                        div('col-sm-6', [
                            form_text('prename', _('First name'), $preName)
                        ]),
                        div('col-sm-6', [
                            form_text('lastname', _('Last name'), $lastName)
                        ])
                    ]),
                    div('row', [
                        div('col-sm-3', [
                            form_text('age', _('Age'), $age)
                        ]),
                        div('col-sm-9', [
                            form_text('hometown', _('Hometown'), $hometown)
                        ])
                    ]),
                    form_info(entry_required() . ' = ' . _('Entry required!'))
                ])
            ]),
            // form_textarea('comment', _('Did you help at former CCC events and which tasks have you performed then?'), $comment),
            form_submit('submit', _('Register'))
        ])
    ]);
}

/**
 * @return string
 */
function entry_required()
{
    return '<span class="text-info glyphicon glyphicon-warning-sign"></span>';
}

/**
 * @return bool
 */
function guest_logout()
{
    session()->invalidate();
    redirect(page_link_to('start'));
    return true;
}

/**
 * @return string
 */
function guest_login()
{
    $nick = '';
    $request = request();
    $session = session();
    $valid = true;

    $session->remove('uid');

    if ($request->has('submit')) {
        if ($request->has('nick') && strlen(User_validate_Nick($request->input('nick'))) > 0) {
            $nick = User_validate_Nick($request->input('nick'));
            $login_user = DB::selectOne('SELECT * FROM `User` WHERE `Nick`=?', [$nick]);
            if (!empty($login_user)) {
                if ($request->has('password')) {
                    if (!verify_password($request->postData('password'), $login_user['Passwort'], $login_user['UID'])) {
                        $valid = false;
                        error(_('Your password is incorrect.  Please try it again.'));
                    }
                } else {
                    $valid = false;
                    error(_('Please enter a password.'));
                }
            } else {
                $valid = false;
                error(_('No user was found with that Nickname. Please try again. If you are still having problems, ask a Dispatcher.'));
            }
        } else {
            $valid = false;
            error(_('Please enter a nickname.'));
        }

        if ($valid && !empty($login_user)) {
            $session->set('uid', $login_user['UID']);
            $session->set('locale', $login_user['Sprache']);

            redirect(page_link_to('news'));
        }
    }

    $event_config = EventConfig();

    return page([
        div('col-md-12', [
            div('row', [
                EventConfig_countdown_page($event_config)
            ]),
            div('row', [
                div('col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4', [
                    div('panel panel-primary first', [
                        div('panel-heading', [
                            '<span class="icon-icon_angel"></span> ' . _('Login')
                        ]),
                        div('panel-body', [
                            msg(),
                            form([
                                form_text_placeholder('nick', _('Nick'), $nick),
                                form_password_placeholder('password', _('Password')),
                                form_submit('submit', _('Login')),
                                !$valid ? buttons([
                                    button(page_link_to('user_password_recovery'), _('I forgot my password'))
                                ]) : ''
                            ])
                        ]),
                        div('panel-footer', [
                            glyph('info-sign') . _('Please note: You have to activate cookies!')
                        ])
                    ])
                ])
            ]),
            div('row', [
                div('col-sm-6 text-center', [
                    heading(register_title(), 2),
                    get_register_hint()
                ]),
                div('col-sm-6 text-center', [
                    heading(_('What can I do?'), 2),
                    '<p>' . _('Please read about the jobs you can do to help us.') . '</p>',
                    buttons([
                        button(
                            page_link_to('angeltypes', ['action' => 'about']),
                            _('Teams/Job description') . ' &raquo;'
                        )
                    ])
                ])
            ])
        ])
    ]);
}

/**
 * @return string
 */
function get_register_hint()
{
    global $privileges;

    if (in_array('register', $privileges) && config('registration_enabled')) {
        return join('', [
            '<p>' . _('Please sign up, if you want to help us!') . '</p>',
            buttons([
                button(page_link_to('register'), register_title() . ' &raquo;')
            ])
        ]);
    }

    return error(_('Registration is disabled.'), true);
}
