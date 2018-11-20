<?php

use Carbon\Carbon;
use Engelsystem\Models\User\User;

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
 * @param User  $user_source The user
 * @param bool  $enable_tshirt_size
 * @param array $tshirt_sizes
 * @return User
 */
function user_settings_main($user_source, $enable_tshirt_size, $tshirt_sizes)
{
    $valid = true;
    $request = request();

    if ($request->has('mail')) {
        $result = User_validate_mail($request->input('mail'));
        $user_source->email = $result->getValue();
        if (!$result->isValid()) {
            $valid = false;
            error(__('E-mail address is not correct.'));
        }
    } else {
        $valid = false;
        error(__('Please enter your e-mail.'));
    }

    $user_source->settings->email_shiftinfo = $request->has('email_shiftinfo');
    $user_source->settings->email_human = $request->has('email_by_human_allowed');

    if ($request->has('tshirt_size') && isset($tshirt_sizes[$request->input('tshirt_size')])) {
        $user_source->personalData->shirt_size = $request->input('tshirt_size');
    } elseif ($enable_tshirt_size) {
        $valid = false;
    }

    if ($request->has('planned_arrival_date')) {
        $tmp = parse_date('Y-m-d H:i', $request->input('planned_arrival_date') . ' 00:00');
        $result = User_validate_planned_arrival_date($tmp);
        $user_source->personalData->planned_arrival_date = Carbon::createFromTimestamp($result->getValue());
        if (!$result->isValid()) {
            $valid = false;
            error(__('Please enter your planned date of arrival. It should be after the buildup start date and before teardown end date.'));
        }
    }

    if ($request->has('planned_departure_date')) {
        $tmp = parse_date('Y-m-d H:i', $request->input('planned_departure_date') . ' 00:00');
        $plannedArrivalDate = $user_source->personalData->planned_arrival_date;
        $result = User_validate_planned_departure_date(
            $plannedArrivalDate ? $plannedArrivalDate->getTimestamp() : 0,
            $tmp
        );
        $user_source->personalData->planned_departure_date = Carbon::createFromTimestamp($result->getValue());
        if (!$result->isValid()) {
            $valid = false;
            error(__('Please enter your planned date of departure. It should be after your planned arrival date and after buildup start date and before teardown end date.'));
        }
    }

    // Trivia
    $user_source->personalData->last_name = strip_request_item('lastname', $user_source['Name']);
    $user_source->personalData->first_name = strip_request_item('prename', $user_source['Vorname']);
    if (strlen(strip_request_item('dect')) <= 5) {
        $user_source->contact->dect = strip_request_item('dect', $user_source['DECT']);
    } else {
        $valid = false;
        error(__('For dect numbers are only 5 digits allowed.'));
    }
    $user_source->contact->mobile = strip_request_item('mobile', $user_source['Handy']);

    if ($valid) {
        $user_source->save();
        $user_source->contact->save();
        $user_source->personalData->save();
        $user_source->settings->save();

        success(__('Settings saved.'));
        redirect(page_link_to('user_settings'));
    }

    return $user_source;
}

/**
 * Change user password.
 *
 * @param User $user_source The user
 */
function user_settings_password($user_source)
{
    $request = request();
    if (
        !$request->has('password')
        || !verify_password($request->postData('password'), $user_source->password, $user_source->id)
    ) {
        error(__('-> not OK. Please try again.'));
    } elseif (strlen($request->postData('new_password')) < config('min_password_length')) {
        error(__('Your password is to short (please use at least 6 characters).'));
    } elseif ($request->postData('new_password') != $request->postData('new_password2')) {
        error(__('Your passwords don\'t match.'));
    } else {
        set_password($user_source->id, $request->postData('new_password'));
        success(__('Password saved.'));
    }
    redirect(page_link_to('user_settings'));
}

/**
 * Change user theme
 *
 * @param User  $user_source The user
 * @param array $themes      List of available themes
 * @return User
 */
function user_settings_theme($user_source, $themes)
{
    $valid = true;
    $request = request();

    if ($request->has('theme') && isset($themes[$request->input('theme')])) {
        $user_source->settings->theme = $request->input('theme');
    } else {
        $valid = false;
    }

    if ($valid) {
        $user_source->settings->save();

        success(__('Theme changed.'));
        redirect(page_link_to('user_settings'));
    }

    return $user_source;
}

/**
 * Change use locale
 *
 * @param User  $user_source The user
 * @param array $locales     List of available locales
 * @return User
 */
function user_settings_locale($user_source, $locales)
{
    $valid = true;
    $request = request();
    $session = session();

    if ($request->has('language') && isset($locales[$request->input('language')])) {
        $user_source->settings->language = $request->input('language');
    } else {
        $valid = false;
    }

    if ($valid) {
        $user_source->settings->save();
        $session->set('locale', $user_source->settings->language);

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

    $user_source = auth()->user();
    if ($request->hasPostData('submit')) {
        $user_source = user_settings_main($user_source, $enable_tshirt_size, $tshirt_sizes);
    } elseif ($request->hasPostData('submit_password')) {
        user_settings_password($user_source);
    } elseif ($request->hasPostData('submit_theme')) {
        $user_source = user_settings_theme($user_source, $themes);
    } elseif ($request->hasPostData('submit_language')) {
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
