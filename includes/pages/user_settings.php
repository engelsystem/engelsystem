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
    $user_source->settings->email_news = $request->has('email_news');
    if (config('enable_goody')) {
        $user_source->settings->email_goody = $request->has('email_goody');
    }

    if ($request->has('tshirt_size') && isset($tshirt_sizes[$request->input('tshirt_size')])) {
        $user_source->personalData->shirt_size = $request->input('tshirt_size');
    } elseif ($enable_tshirt_size) {
        $valid = false;
    }

    if ($request->has('planned_arrival_date') && $request->input('planned_arrival_date')) {
        $tmp = parse_date('Y-m-d H:i', $request->input('planned_arrival_date') . ' 00:00');
        $result = User_validate_planned_arrival_date($tmp);
        $user_source->personalData->planned_arrival_date = Carbon::createFromTimestamp($result->getValue());
        if (!$result->isValid()) {
            $valid = false;
            error(__('Please enter your planned date of arrival. It should be after the buildup start date and before teardown end date.'));
        }
    }

    if ($request->has('planned_departure_date') && $request->input('planned_departure_date')) {
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
    $pronoun = strip_request_item('pronoun', $user_source->personalData->pronoun);
    if (config('enable_pronoun') && mb_strlen($pronoun) <= 15) {
        $user_source->personalData->pronoun = $pronoun;
    }
    if (config('enable_user_name')) {
        $user_source->personalData->last_name = strip_request_item('lastname', $user_source->personalData->last_name);
        $user_source->personalData->first_name = strip_request_item('prename', $user_source->personalData->first_name);
    }
    if (config('enable_dect')) {
        if (strlen(strip_request_item('dect')) <= 40) {
            $user_source->contact->dect = strip_request_item('dect', $user_source->contact->dect);
        } else {
            $valid = false;
            error(__('For dect numbers are only 40 digits allowed.'));
        }
    }
    $user_source->contact->mobile = strip_request_item('mobile', $user_source->contact->mobile);

    if ($valid) {
        $user_source->save();
        $user_source->contact->save();
        $user_source->personalData->save();
        $user_source->settings->save();

        success(__('Settings saved.'));
        throw_redirect(page_link_to('user_settings'));
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

    $enable_tshirt_size = config('enable_tshirt_size');
    $tshirt_sizes = config('tshirt_sizes');

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
    }

    return User_settings_view(
        $user_source,
        $buildup_start_date,
        $teardown_end_date,
        $enable_tshirt_size,
        $tshirt_sizes
    );
}
