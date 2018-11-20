<?php

use Carbon\Carbon;
use Engelsystem\Models\EventConfig;

/**
 * @return string
 */
function event_config_title()
{
    return __('Event config');
}

/**
 * @return array
 */
function event_config_edit_controller()
{
    global $privileges;

    if (!in_array('admin_event_config', $privileges)) {
        redirect(page_link_to('/'));
    }

    $request = request();
    $config = config();
    $event_name = $config->get('name');
    $event_welcome_msg = $config->get('welcome_msg');
    /** @var Carbon $buildup_start_date */
    $buildup_start_date = $config->get('buildup_start');
    /** @var Carbon $event_start_date */
    $event_start_date = $config->get('event_start');
    /** @var Carbon $event_end_date */
    $event_end_date = $config->get('event_end');
    /** @var Carbon $teardown_end_date */
    $teardown_end_date = $config->get('teardown_end');

    if ($request->hasPostData('submit')) {
        $valid = true;

        if ($request->has('event_name')) {
            $event_name = strip_request_item('event_name');
        }
        if ($event_name == '') {
            $event_name = null;
        }

        if ($request->has('event_welcome_msg')) {
            $event_welcome_msg = strip_request_item_nl('event_welcome_msg');
        }
        if ($event_welcome_msg == '') {
            $event_welcome_msg = null;
        }

        $result = check_request_date('buildup_start_date', __('Please enter buildup start date.'), true);
        $buildup_start_date = $result->getValue();
        $valid &= $result->isValid();

        $result = check_request_date('event_start_date', __('Please enter event start date.'), true);
        $event_start_date = $result->getValue();
        $valid &= $result->isValid();

        $result = check_request_date('event_end_date', __('Please enter event end date.'), true);
        $event_end_date = $result->getValue();
        $valid &= $result->isValid();

        $result = check_request_date('teardown_end_date', __('Please enter teardown end date.'), true);
        $teardown_end_date = $result->getValue();
        $valid &= $result->isValid();

        if (!is_null($buildup_start_date) && !is_null($event_start_date) && $buildup_start_date > $event_start_date) {
            $valid = false;
            error(__('The buildup start date has to be before the event start date.'));
        }

        if (!is_null($event_start_date) && !is_null($event_end_date) && $event_start_date > $event_end_date) {
            $valid = false;
            error(__('The event start date has to be before the event end date.'));
        }

        if (!is_null($event_end_date) && !is_null($teardown_end_date) && $event_end_date > $teardown_end_date) {
            $valid = false;
            error(__('The event end date has to be before the teardown end date.'));
        }

        if (!is_null($buildup_start_date) && !is_null($teardown_end_date) && $buildup_start_date > $teardown_end_date) {
            $valid = false;
            error(__('The buildup start date has to be before the teardown end date.'));
        }

        if ($valid) {
            $eventConfig = new EventConfig();

            foreach (
                [
                    'name'          => $event_name,
                    'welcome_msg'   => $event_welcome_msg,
                    'buildup_start' => $buildup_start_date,
                    'event_start'   => $event_start_date,
                    'event_end'     => $event_end_date,
                    'teardown_end'  => $teardown_end_date,
                ] as $key => $value
            ) {
                $eventConfig
                    ->findOrNew($key)
                    ->setAttribute('name', $key)
                    ->setAttribute('value', $value)
                    ->save();
            }

            engelsystem_log(
                sprintf(
                    'Changed event config: %s, %s, %s, %s, %s, %s',
                    $event_name,
                    $event_welcome_msg,
                    $buildup_start_date ? $buildup_start_date->format('Y-m-d') : '',
                    $event_start_date ? $event_start_date->format('Y-m-d') : '',
                    $event_end_date ? $event_end_date->format('Y-m-d') : '',
                    $teardown_end_date ? $teardown_end_date->format('Y-m-d') : ''
                )
            );
            success(__('Settings saved.'));
            redirect(page_link_to('admin_event_config'));
        }
    }

    return [
        event_config_title(),
        EventConfig_edit_view(
            $event_name,
            $event_welcome_msg,
            $buildup_start_date,
            $event_start_date,
            $event_end_date,
            $teardown_end_date
        )
    ];
}
