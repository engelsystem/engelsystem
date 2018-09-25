<?php

use Carbon\Carbon;
use Engelsystem\Database\DB;

/**
 * @return string
 */
function settings_title()
{
    return __('Settings');
}

/**
 * Change user main attributes (name, dates, etc.)
 *
 * @param array $user_source The user
 * @param bool  $enable_tshirt_size
 * @param array $tshirt_sizes
 * @return array
 */
function user_settings_main($user_source, $enable_tshirt_size, $tshirt_sizes)
{
    $valid = true;
    $request = request();

    if ($request->has('mail')) {
        $result = User_validate_mail($request->input('mail'));
        $user_source['email'] = $result->getValue();
        if (!$result->isValid()) {
            $valid = false;
            error(__('E-mail address is not correct.'));
        }
    } else {
        $valid = false;
        error(__('Please enter your e-mail.'));
    }

    $user_source['email_shiftinfo'] = $request->has('email_shiftinfo');
    $user_source['email_by_human_allowed'] = $request->has('email_by_human_allowed');

    if ($request->has('jabber')) {
        $result = User_validate_jabber($request->input('jabber'));
        $user_source['jabber'] = $result->getValue();
        if (!$result->isValid()) {
            $valid = false;
            error(__('Please check your jabber account information.'));
        }
    }

    if ($request->has('tshirt_size') && isset($tshirt_sizes[$request->input('tshirt_size')])) {
        $user_source['Size'] = $request->input('tshirt_size');
    } elseif ($enable_tshirt_size) {
        $valid = false;
    }

    if ($request->has('planned_arrival_date')) {
        $tmp = parse_date('Y-m-d H:i', $request->input('planned_arrival_date') . ' 00:00');
        $result = User_validate_planned_arrival_date($tmp);
        $user_source['planned_arrival_date'] = $result->getValue();
        if (!$result->isValid()) {
            $valid = false;
            error(__('Please enter your planned date of arrival. It should be after the buildup start date and before teardown end date.'));
        }
    }

    if ($request->has('planned_departure_date')) {
        $tmp = parse_date('Y-m-d H:i', $request->input('planned_departure_date') . ' 00:00');
        $result = User_validate_planned_departure_date($user_source['planned_arrival_date'], $tmp);
        $user_source['planned_departure_date'] = $result->getValue();
        if (!$result->isValid()) {
            $valid = false;
            error(__('Please enter your planned date of departure. It should be after your planned arrival date and after buildup start date and before teardown end date.'));
        }
    }

    // Trivia
    $user_source['Name'] = strip_request_item('lastname', $user_source['Name']);
    $user_source['Vorname'] = strip_request_item('prename', $user_source['Vorname']);
    $user_source['Alter'] = strip_request_item('age', $user_source['Alter']);
    $user_source['Telefon'] = strip_request_item('tel', $user_source['Telefon']);
    if (strlen(strip_request_item('dect')) <= 5) {
        $user_source['DECT'] = strip_request_item('dect', $user_source['DECT']);
    } else {
        $valid = false;
        error(__('For dect numbers are only 5 digits allowed.'));
    }
    $user_source['Handy'] = strip_request_item('mobile', $user_source['Handy']);
    $user_source['Hometown'] = strip_request_item('hometown', $user_source['Hometown']);

    if ($valid) {
        User_update($user_source);

        success(__('Settings saved.'));
        redirect(page_link_to('user_settings'));
    }

    return $user_source;
}

/**
 * Change user password.
 *
 * @param array $user_source The user
 */
function user_settings_password($user_source)
{
    $request = request();
    if (
        !$request->has('password')
        || !verify_password($request->postData('password'), $user_source['Passwort'], $user_source['UID'])
    ) {
        error(__('-> not OK. Please try again.'));
    } elseif (strlen($request->postData('new_password')) < config('min_password_length')) {
        error(__('Your password is to short (please use at least 6 characters).'));
    } elseif ($request->postData('new_password') != $request->postData('new_password2')) {
        error(__('Your passwords don\'t match.'));
    } else {
        set_password($user_source['UID'], $request->postData('new_password'));
        success(__('Password saved.'));
    }
    redirect(page_link_to('user_settings'));
}

/**
 * Change user theme
 *
 * @param array $user_source The user
 * @param array $themes      List of available themes
 * @return array
 */
function user_settings_theme($user_source, $themes)
{
    $valid = true;
    $request = request();

    if ($request->has('theme') && isset($themes[$request->input('theme')])) {
        $user_source['color'] = $request->input('theme');
    } else {
        $valid = false;
    }

    if ($valid) {
        DB::update('
            UPDATE `User`
            SET `color`=?
            WHERE `UID`=?
            ',
            [
                $user_source['color'],
                $user_source['UID'],
            ]
        );

        success(__('Theme changed.'));
        redirect(page_link_to('user_settings'));
    }

    return $user_source;
}

/**
 * Change use locale
 *
 * @param array $user_source The user
 * @param array $locales     List of available locales
 * @return array
 */
function user_settings_locale($user_source, $locales)
{
    $valid = true;
    $request = request();
    $session = session();

    if ($request->has('language') && isset($locales[$request->input('language')])) {
        $user_source['Sprache'] = $request->input('language');
    } else {
        $valid = false;
    }

    if ($valid) {
        DB::update('
            UPDATE `User`
            SET `Sprache`=?
            WHERE `UID`=?
        ',
            [
                $user_source['Sprache'],
                $user_source['UID'],
            ]
        );
        $session->set('locale', $user_source['Sprache']);

        success('Language changed.');
        redirect(page_link_to('user_settings'));
    }

    return $user_source;
}

/**
 * Main user settings page/controller
 *
 * @return string
 */
function user_settings()
{
    global $user;
    $request = request();
    $config = config();
    $themes = config('available_themes');

    $enable_tshirt_size = config('enable_tshirt_size');
    $tshirt_sizes = config('tshirt_sizes');
    $locales = config('locales');

    $buildup_start_date = null;
    $teardown_end_date = null;

    if ($buildup = $config->get('buildup_start')) {
        /** @var Carbon $buildup */
        $buildup_start_date = $buildup->getTimestamp();
    }

    if ($teardown = $config->get('teardown_end')) {
        /** @var Carbon $teardown */
        $teardown_end_date = $teardown->getTimestamp();
    }

    $user_source = $user;

    if ($request->has('submit')) {
        $user_source = user_settings_main($user_source, $enable_tshirt_size, $tshirt_sizes);
    } elseif ($request->has('submit_password')) {
        user_settings_password($user_source);
    } elseif ($request->has('submit_theme')) {
        $user_source = user_settings_theme($user_source, $themes);
    } elseif ($request->has('submit_language')) {
        $user_source = user_settings_locale($user_source, $locales);
    }

    return User_settings_view(
        $user_source,
        $locales,
        $themes,
        $buildup_start_date,
        $teardown_end_date,
        $enable_tshirt_size,
        $tshirt_sizes
    );
}
