<?php

use Carbon\Carbon;
use Engelsystem\Database\DB;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;

/**
 * @return string
 */
function register_title()
{
    return __('Register');
}

/**
 * Engel registrieren
 *
 * @return string
 */
function guest_register()
{
    $authUser = auth()->user();
    $tshirt_sizes = config('tshirt_sizes');
    $enable_tshirt_size = config('enable_tshirt_size');
    $enable_user_name = config('enable_user_name');
    $enable_dect = config('enable_dect');
    $enable_planned_arrival = config('enable_planned_arrival');
    $min_password_length = config('min_password_length');
    $enable_pronoun = config('enable_pronoun');
    $config = config();
    $request = request();
    $session = session();

    $msg = '';
    $nick = '';
    $lastName = '';
    $preName = '';
    $dect = '';
    $mobile = '';
    $email = '';
    $pronoun = '';
    $email_shiftinfo = false;
    $email_by_human_allowed = false;
    $email_news = false;
    $tshirt_size = '';
    $password_hash = '';
    $selected_angel_types = [];
    $planned_arrival_date = null;

    $angel_types_source = AngelTypes();
    $angel_types = [];
    foreach ($angel_types_source as $angel_type) {
        $angel_types[$angel_type['id']] = $angel_type['name']
            . ($angel_type['restricted'] ? ' (' . __('Requires introduction') . ')' : '');
        if (!$angel_type['restricted']) {
            $selected_angel_types[] = $angel_type['id'];
        }
    }

    if (!auth()->can('register') || (!$authUser && !config('registration_enabled'))) {
        error(__('Registration is disabled.'));

        return page_with_title(register_title(), [
            msg(),
        ]);
    }

    if ($request->hasPostData('submit')) {
        $valid = true;

        if ($request->has('username')) {
            $nickValidation = User_validate_Nick($request->input('username'));
            $nick = $nickValidation->getValue();

            if (!$nickValidation->isValid()) {
                $valid = false;
                $msg .= error(sprintf(__('Please enter a valid nick.') . ' ' . __('Use up to 24 letters, numbers, connecting punctuations or spaces for your nickname.'),
                    $nick), true);
            }
            if (User::whereName($nick)->count() > 0) {
                $valid = false;
                $msg .= error(sprintf(__('Your nick &quot;%s&quot; already exists.'), $nick), true);
            }
        } else {
            $valid = false;
            $msg .= error(__('Please enter a nickname.'), true);
        }

        if ($request->has('email') && strlen(strip_request_item('email')) > 0) {
            $email = strip_request_item('email');
            if (!check_email($email)) {
                $valid = false;
                $msg .= error(__('E-mail address is not correct.'), true);
            }
            if (User::whereEmail($email)->first()) {
                $valid = false;
                $msg .= error(__('E-mail address is already used by another user.'), true);
            }
        } else {
            $valid = false;
            $msg .= error(__('Please enter your e-mail.'), true);
        }

        if ($request->has('email_shiftinfo')) {
            $email_shiftinfo = true;
        }

        if ($request->has('email_by_human_allowed')) {
            $email_by_human_allowed = true;
        }

        if ($request->has('email_news')) {
            $email_news = true;
        }

        if ($enable_tshirt_size) {
            if ($request->has('tshirt_size') && isset($tshirt_sizes[$request->input('tshirt_size')])) {
                $tshirt_size = $request->input('tshirt_size');
            } else {
                $valid = false;
                $msg .= error(__('Please select your shirt size.'), true);
            }
        }

        if ($request->has('password') && strlen($request->postData('password')) >= $min_password_length) {
            if ($request->postData('password') != $request->postData('password2')) {
                $valid = false;
                $msg .= error(__('Your passwords don\'t match.'), true);
            }
        } else {
            $valid = false;
            $msg .= error(sprintf(
                __('Your password is too short (please use at least %s characters).'),
                $min_password_length
            ), true);
        }

        if ($request->has('planned_arrival_date') && $enable_planned_arrival) {
            $tmp = parse_date('Y-m-d H:i', $request->input('planned_arrival_date') . ' 00:00');
            $result = User_validate_planned_arrival_date($tmp);
            $planned_arrival_date = $result->getValue();
            if (!$result->isValid()) {
                $valid = false;
                error(__('Please enter your planned date of arrival. It should be after the buildup start date and before teardown end date.'));
            }
        } elseif ($enable_planned_arrival) {
            $valid = false;
            error(__('Please enter your planned date of arrival. It should be after the buildup start date and before teardown end date.'));
        }

        $selected_angel_types = [];
        foreach (array_keys($angel_types) as $angel_type_id) {
            if ($request->has('angel_types_' . $angel_type_id)) {
                $selected_angel_types[] = $angel_type_id;
            }
        }

        // Trivia
        if ($enable_user_name && $request->has('lastname')) {
            $lastName = strip_request_item('lastname');
        }
        if ($enable_user_name && $request->has('prename')) {
            $preName = strip_request_item('prename');
        }
        if ($enable_pronoun && $request->has('pronoun')) {
            $pronoun = strip_request_item('pronoun');
        }
        if ($enable_dect && $request->has('dect')) {
            if (strlen(strip_request_item('dect')) <= 40) {
                $dect = strip_request_item('dect');
            } else {
                $valid = false;
                error(__('For dect numbers are only 40 digits allowed.'));
            }
        }
        if ($request->has('mobile')) {
            $mobile = strip_request_item('mobile');
        }

        if ($valid) {
            $user = new User([
                'name'          => $nick,
                'password'      => $password_hash,
                'email'         => $email,
                'api_key'       => '',
                'last_login_at' => null,
            ]);
            $user->save();

            $contact = new Contact([
                'dect'   => $dect,
                'mobile' => $mobile,
            ]);
            $contact->user()
                ->associate($user)
                ->save();

            $personalData = new PersonalData([
                'first_name'           => $preName,
                'last_name'            => $lastName,
                'pronoun'              => $pronoun,
                'shirt_size'           => $tshirt_size,
                'planned_arrival_date' => $enable_planned_arrival ? Carbon::createFromTimestamp($planned_arrival_date) : null,
            ]);
            $personalData->user()
                ->associate($user)
                ->save();

            $settings = new Settings([
                'language'        => $session->get('locale'),
                'theme'           => config('theme'),
                'email_human'     => $email_by_human_allowed,
                'email_shiftinfo' => $email_shiftinfo,
                'email_news'      => $email_news,
            ]);
            $settings->user()
                ->associate($user)
                ->save();

            $state = new State([]);
            if (config('autoarrive')) {
                $state->arrived = true;
                $state->arrival_date = new Carbon();
            }
            $state->user()
                ->associate($user)
                ->save();

            if ($session->has('oauth2_connect_provider') && $session->has('oauth2_user_id')) {
                $oauth = new OAuth([
                    'provider'      => $session->get('oauth2_connect_provider'),
                    'identifier'    => $session->get('oauth2_user_id'),
                    'access_token'  => $session->get('oauth2_access_token'),
                    'refresh_token' => $session->get('oauth2_refresh_token'),
                    'expires_at'    => $session->get('oauth2_expires_at'),
                ]);
                $oauth->user()
                    ->associate($user)
                    ->save();

                $session->remove('oauth2_connect_provider');
                $session->remove('oauth2_user_id');
                $session->remove('oauth2_access_token');
                $session->remove('oauth2_refresh_token');
                $session->remove('oauth2_expires_at');
            }

            // Assign user-group and set password
            DB::insert('INSERT INTO `UserGroups` (`uid`, `group_id`) VALUES (?, -20)', [$user->id]);
            auth()->setPassword($user, $request->postData('password'));

            // Assign angel-types
            $user_angel_types_info = [];
            foreach ($selected_angel_types as $selected_angel_type_id) {
                DB::insert(
                    'INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`, `supporter`) VALUES (?, ?, FALSE)',
                    [$user->id, $selected_angel_type_id]
                );
                $user_angel_types_info[] = $angel_types[$selected_angel_type_id];
            }

            engelsystem_log(
                'User ' . User_Nick_render($user, true)
                . ' signed up as: ' . join(', ', $user_angel_types_info)
            );
            success(__('Angel registration successful!'));

            // User is already logged in - that means a supporter has registered an angel. Return to register page.
            if ($authUser) {
                throw_redirect(page_link_to('register'));
            }

            // If a welcome message is present, display it on the next page
            if ($message = $config->get('welcome_msg')) {
                info((new Parsedown())->text($message));
            }

            // Login the user
            if ($user->oauth->count()) {
                /** @var OAuth $provider */
                $provider = $user->oauth->first();
                throw_redirect(url('/oauth/' . $provider->provider));
            }

            throw_redirect(page_link_to('/'));
        }
    }

    $buildup_start_date = time();
    $teardown_end_date = null;
    if ($buildup = $config->get('buildup_start')) {
        /** @var Carbon $buildup */
        $buildup_start_date = $buildup->getTimestamp();
    }

    if ($teardown = $config->get('teardown_end')) {
        /** @var Carbon $teardown */
        $teardown_end_date = $teardown->getTimestamp();
    }

    $form_data = $session->get('form_data');
    $session->remove('form_data');
    if (!$nick && !empty($form_data['name'])) {
        $nick = $form_data['name'];
    }

    if (!$email && !empty($form_data['email'])) {
        $email = $form_data['email'];
    }

    if (!$preName && !empty($form_data['first_name'])) {
        $preName = $form_data['first_name'];
    }

    if (!$lastName && !empty($form_data['last_name'])) {
        $lastName = $form_data['last_name'];
    }

    return page_with_title(register_title(), [
        __('By completing this form you\'re registering as a Chaos-Angel. This script will create you an account in the angel task scheduler.'),
        $msg,
        msg(),
        form([
            div('row', [
                div('col-md-6', [
                    div('row', [
                        div('col-sm-4', [
                            form_text(
                                'username',
                                __('Nick') . ' ' . entry_required(),
                                $nick,
                                false,
                                24,
                                'nickname'
                            ),
                            form_info('',
                                __('Use up to 24 letters, numbers, connecting punctuations or spaces for your nickname.'))
                        ]),
                        div('col-sm-8', [
                            form_email(
                                'email',
                                __('E-Mail') . ' ' . entry_required(),
                                $email,
                                false,
                                'email',
                                254
                            ),
                            form_checkbox(
                                'email_shiftinfo',
                                __(
                                    'The %s is allowed to send me an email (e.g. when my shifts change)',
                                    [config('app_name')]
                                ),
                                $email_shiftinfo
                            ),
                            form_checkbox(
                                'email_news',
                                __('Notify me of new news'),
                                $email_news
                            ),
                            form_checkbox(
                                'email_by_human_allowed',
                                __('Humans are allowed to send me an email (e.g. for ticket vouchers)'),
                                $email_by_human_allowed
                            ),
                        ])
                    ]),
                    div('row', [
                        div('col-sm-6', [
                            form_password('password', __('Password') . ' ' . entry_required())
                        ]),
                        div('col-sm-6', [
                            form_password('password2', __('Confirm password') . ' ' . entry_required())
                        ])
                    ]),
                    div('row', [
                        $enable_planned_arrival ? div('col-sm-6', [
                            form_date(
                                'planned_arrival_date',
                                __('Planned date of arrival') . ' ' . entry_required(),
                                $planned_arrival_date, $buildup_start_date, $teardown_end_date
                            )
                        ]) : '',
                        div('col-sm-6', [
                            $enable_tshirt_size ? form_select('tshirt_size',
                                __('Shirt size') . ' ' . entry_required(),
                                $tshirt_sizes, $tshirt_size, __('Please select...')) : ''
                        ])
                    ]),
                    form_checkboxes(
                        'angel_types',
                        __('What do you want to do?') . sprintf(
                            ' (<a href="%s">%s</a>)',
                            page_link_to('angeltypes', ['action' => 'about']),
                            __('Description of job types')
                        ),
                        $angel_types,
                        $selected_angel_types
                    ),
                    form_info(
                        '',
                        __('Some angel types have to be confirmed later by a supporter at an introduction meeting. You can change your selection in the options section.')
                    )
                ]),
                div('col-md-6', [
                    div('row', [
                        $enable_dect ? div('col-sm-4', [
                            form_text('dect', __('DECT'), $dect, false, 40, 'tel-local')
                        ]) : '',
                        div($enable_dect ? 'col-sm-4' : 'col-sm-12', [
                            form_text('mobile', __('Mobile'), $mobile, false, 40, 'tel-national')
                        ]),
                        $enable_pronoun ? div('col-sm-4', [
                            form_text('pronoun', __('Pronoun'), $pronoun, false, 15)
                        ]) : '',
                    ]),
                    $enable_user_name ? div('row', [
                        div('col-sm-6', [
                            form_text('prename', __('First name'), $preName, false, 64, 'given-name')
                        ]),
                        div('col-sm-6', [
                            form_text('lastname', __('Last name'), $lastName, false, 64, 'family-name')
                        ])
                    ]) : '',
                    form_info(entry_required() . ' = ' . __('Entry required!'))
                ])
            ]),
            form_submit('submit', __('Register'))
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
