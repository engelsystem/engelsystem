<?php

use Carbon\Carbon;
use Engelsystem\Database\Database;
use Engelsystem\Events\Listener\OAuth2;
use Engelsystem\Config\GoodieType;
use Engelsystem\Http\Validation\Rules\Username;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Group;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;

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
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;
    $enable_user_name = config('enable_user_name');
    $enable_dect = config('enable_dect');
    $enable_planned_arrival = config('enable_planned_arrival');
    $min_password_length = config('min_password_length');
    $enable_password = config('enable_password');
    $enable_pronoun = config('enable_pronoun');
    $enable_mobile_show = config('enable_mobile_show');
    $config = config();
    $request = request();
    $session = session();
    /** @var Connection $db */
    $db = app(Database::class)->getConnection();
    $is_oauth = $session->has('oauth2_connect_provider');

    $msg = '';
    $nick = '';
    $lastName = '';
    $preName = '';
    $dect = '';
    $mobile = '';
    $mobile_show = false;
    $email = '';
    $pronoun = '';
    $email_shiftinfo = false;
    $email_by_human_allowed = false;
    $email_messages = false;
    $email_news = false;
    $email_goody = false;
    $tshirt_size = '';
    $password_hash = '';
    $selected_angel_types = [];
    $planned_arrival_date = null;

    /** @var AngelType[]|Collection $angel_types_source */
    $angel_types_source = AngelType::all();
    $angel_types = [];
    if (!empty($session->get('oauth2_groups'))) {
        /** @var OAuth2 $oauth */
        $oauth = app()->get(OAuth2::class);
        $ssoTeams = $oauth->getSsoTeams($session->get('oauth2_connect_provider'));
        foreach ($ssoTeams as $name => $team) {
            if (in_array($name, $session->get('oauth2_groups'))) {
                $selected_angel_types[] = $team['id'];
            }
        }
    }
    foreach ($angel_types_source as $angel_type) {
        if ($angel_type->hide_register) {
            continue;
        }
        $angel_types[$angel_type->id] = $angel_type->name
            . ($angel_type->restricted ? ' (' . __('Requires introduction') . ')' : '');
        if (!$angel_type->restricted) {
            $selected_angel_types[] = $angel_type->id;
        }
    }

    $oauth_enable_password = $session->get('oauth2_enable_password');
    if (!is_null($oauth_enable_password)) {
        $enable_password = $oauth_enable_password;
    }

    if (
        !auth()->can('register') // No registration permission
        // Not authenticated and
        || (!$authUser && !config('registration_enabled') && !$session->get('oauth2_allow_registration')) // Registration disabled
        || (!$authUser && !$enable_password && !$is_oauth) // Password disabled and not oauth
    ) {
        error(__('Registration is disabled.'));

        return page_with_title(register_title(), [
            msg(),
        ]);
    }

    if ($request->hasPostData('submit')) {
        $valid = true;

        if ($request->has('username')) {
            $nick = trim($request->get('username'));
            $nickValid = (new Username())->validate($nick);

            if (!$nickValid) {
                $valid = false;
                $msg .= error(sprintf(
                    __('Please enter a valid nick.') . ' ' . __('Use up to 24 letters, numbers or connecting punctuations for your nickname.'),
                    $nick
                ), true);
            }
            if (User::whereName($nick)->count() > 0) {
                $valid = false;
                $msg .= error(sprintf(__('Your nick "%s" already exists.'), htmlspecialchars($nick)), true);
            }
        } else {
            $valid = false;
            $msg .= error(__('Please enter a nickname.'), true);
        }

        if ($request->has('mobile_show') && $enable_mobile_show) {
            $mobile_show = true;
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

        if ($request->has('email_messages')) {
            $email_messages = true;
        }

        if ($request->has('email_news')) {
            $email_news = true;
        }

        if ($request->has('email_goody')) {
            $email_goody = true;
        }

        if ($goodie_tshirt) {
            if ($request->has('tshirt_size') && isset($tshirt_sizes[$request->input('tshirt_size')])) {
                $tshirt_size = $request->input('tshirt_size');
            } else {
                $valid = false;
                $msg .= error(__('Please select your shirt size.'), true);
            }
        }

        if ($enable_password && $request->has('password') && strlen($request->postData('password')) >= $min_password_length) {
            if ($request->postData('password') != $request->postData('password2')) {
                $valid = false;
                $msg .= error(__('Your passwords don\'t match.'), true);
            }
        } elseif ($enable_password) {
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
            // Safeguard against partially created user data
            $db->beginTransaction();

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
                'email_messages'  => $email_messages,
                'email_goody'     => $email_goody,
                'email_shiftinfo' => $email_shiftinfo,
                'email_news'      => $email_news,
                'mobile_show'     => $mobile_show,
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
            $defaultGroup = Group::find(auth()->getDefaultRole());
            $user->groups()->attach($defaultGroup);
            if ($enable_password) {
                auth()->setPassword($user, $request->postData('password'));
            }

            // Assign angel-types
            $user_angel_types_info = [];
            foreach ($selected_angel_types as $selected_angel_type_id) {
                $angelType = AngelType::findOrFail($selected_angel_type_id);
                $user->userAngelTypes()->attach($angelType);
                $user_angel_types_info[] = $angelType->name;
            }

            // Commit complete user data
            $db->commit();

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
            if ($config->get('welcome_msg')) {
                $session->set('show_welcome', true);
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
        form_info(entry_required() . ' = ' . __('Entry required!')),
        $msg,
        msg(),
        form([
            div('row', [
                div('col', [
                    form_text(
                        'username',
                        __('Nick') . ' ' . entry_required(),
                        $nick,
                        false,
                        24,
                        'nickname'
                    ),
                    form_info(
                        '',
                        __('Use up to 24 letters, numbers or connecting punctuations for your nickname.')
                    ),
                ]),

                $enable_pronoun ? div('col', [
                    form_text('pronoun', __('Pronoun'), $pronoun, false, 15),
                ]) : '',
            ]),

            $enable_user_name ? div('row', [
                div('col', [
                    form_text('prename', __('First name'), $preName, false, 64, 'given-name'),
                ]),
                div('col', [
                    form_text('lastname', __('Last name'), $lastName, false, 64, 'family-name'),
                ]),
            ]) : '',

            div('row', [
                div('col', [
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
                            'settings.profile.email_shiftinfo',
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
                        'email_messages',
                        __('settings.profile.email_messages'),
                        $email_messages
                    ),
                    form_checkbox(
                        'email_by_human_allowed',
                        __('Allow heaven angels to contact you by e-mail.'),
                        $email_by_human_allowed
                    ),
                    $goodie_enabled ?
                        form_checkbox(
                            'email_goody',
                            __('To receive vouchers, give consent that nick, email address, worked hours and shirt size will be stored until the next similar event.')
                            . (config('privacy_email') ? ' ' . __('To withdraw your approval, send an email to <a href="mailto:%s">%1$s</a>.', [config('privacy_email')]) : ''),
                            $email_goody
                        ) : '',
                ]),

                $enable_dect ? div('col', [
                    form_text('dect', __('DECT'), $dect, false, 40, 'tel-local'),
                ]) : '',

                div('col', [
                    form_text('mobile', __('Mobile'), $mobile, false, 40, 'tel-national'),
                    $enable_mobile_show ? form_checkbox(
                        'mobile_show',
                        __('Show mobile number to other users to contact me'),
                        $mobile_show
                    ) : '',
                ]),
            ]),

            div('row', [
                $enable_password ? div('col', [
                    form_password('password', __('Password') . ' ' . entry_required(), 'new-password'),
                ]) : '',

                $enable_planned_arrival ? div('col', [
                    form_date(
                        'planned_arrival_date',
                        __('Planned date of arrival') . ' ' . entry_required(),
                        $planned_arrival_date,
                        $buildup_start_date,
                        $teardown_end_date
                    ),
                ]) : '',
            ]),

            div('row', [
                $enable_password ? div('col', [
                    form_password('password2', __('Confirm password') . ' ' . entry_required(), 'new-password'),
                ]) : '',

                div('col', [
                    $goodie_tshirt ? form_select(
                        'tshirt_size',
                        __('Shirt size') . ' ' . entry_required(),
                        $tshirt_sizes,
                        $tshirt_size,
                        __('form.select_placeholder')
                    ) : '',
                ]),
            ]),

            div('row', [
                div('col', [
                    form_checkboxes(
                        'angel_types',
                        __('What do you want to do?') . sprintf(
                            ' (<a href="%s">%s</a>)',
                            url('/angeltypes/about'),
                            __('Description of job types')
                        ),
                        $angel_types,
                        $selected_angel_types
                    ),
                    form_info(
                        '',
                        __('Some angel types have to be confirmed later by a supporter at an introduction meeting. You can change your selection in the options section.')
                    ),
                ]),
            ]),

            form_submit('submit', __('Register')),
        ]),
    ]);
}

/**
 * @return string
 */
function entry_required()
{
    return icon('exclamation-triangle', 'text-info');
}
