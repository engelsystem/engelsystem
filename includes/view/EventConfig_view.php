<?php

use Carbon\Carbon;

/**
 * Shows basic event infos and countdowns.
 *
 * @return string
 */
function EventConfig_countdown_page()
{
    $config = config();
    $name = $config->get('name', '');
    /** @var Carbon $buildup */
    $buildup = $config->get('buildup_start');
    /** @var Carbon $start */
    $start = $config->get('event_start');
    /** @var Carbon $end */
    $end = $config->get('event_end');
    /** @var Carbon $teardown */
    $teardown = $config->get('teardown_end');
    $elements = [];

    $elements[] = div('col-sm-12 text-center', [
        heading(sprintf(
            __('Welcome to the %s!'),
            $name . ' <span class="icon-icon_angel"></span> ENGELSYSTEM'
        ), 2)
    ]);

    if (!empty($buildup) && $buildup->greaterThan(new Carbon())) {
        $elements[] = div('col-sm-3 text-center hidden-xs', [
            heading(__('Buildup starts'), 4),
            '<span class="moment-countdown text-big" data-timestamp="' . $buildup->getTimestamp() . '">%c</span>',
            '<small>' . $buildup->format(__('Y-m-d')) . '</small>'
        ]);
    }

    if (!empty($start) && $start->greaterThan(new Carbon())) {
        $elements[] = div('col-sm-3 text-center hidden-xs', [
            heading(__('Event starts'), 4),
            '<span class="moment-countdown text-big" data-timestamp="' . $start->getTimestamp() . '">%c</span>',
            '<small>' . $start->format(__('Y-m-d')) . '</small>'
        ]);
    }

    if (!empty($end) && $end->greaterThan(new Carbon())) {
        $elements[] = div('col-sm-3 text-center hidden-xs', [
            heading(__('Event ends'), 4),
            '<span class="moment-countdown text-big" data-timestamp="' . $end->getTimestamp() . '">%c</span>',
            '<small>' . $end->format(__('Y-m-d')) . '</small>'
        ]);
    }

    if (!empty($teardown) && $teardown->greaterThan(new Carbon())) {
        $elements[] = div('col-sm-3 text-center hidden-xs', [
            heading(__('Teardown ends'), 4),
            '<span class="moment-countdown text-big" data-timestamp="' . $teardown->getTimestamp() . '">%c</span>',
            '<small>' . $teardown->format(__('Y-m-d')) . '</small>'
        ]);
    }

    return join('', $elements);
}

/**
 * Render edit page for event config.
 *
 * @param string $event_name         The event name
 * @param string $event_welcome_msg  The welcome message
 * @param int    $buildup_start_date unix time stamp
 * @param int    $event_start_date   unix time stamp
 * @param int    $event_end_date     unix time stamp
 * @param int    $teardown_end_date  unix time stamp
 * @return string
 */
function EventConfig_edit_view(
    $event_name,
    $event_welcome_msg,
    $buildup_start_date,
    $event_start_date,
    $event_end_date,
    $teardown_end_date
) {
    return page_with_title(event_config_title(), [
        msg(),
        form([
            div('row', [
                div('col-md-6', [
                    form_text('event_name', __('Event Name'), $event_name),
                    form_info('', __('Event Name is shown on the start page.')),
                    form_textarea('event_welcome_msg', __('Event Welcome Message'), $event_welcome_msg),
                    form_info(
                        '',
                        __('Welcome message is shown after successful registration. You can use markdown.')
                    )
                ]),
                div('col-md-3 col-xs-6', [
                    form_date('buildup_start_date', __('Buildup date'), $buildup_start_date),
                    form_date('event_start_date', __('Event start date'), $event_start_date)
                ]),
                div('col-md-3 col-xs-6', [
                    form_date('teardown_end_date', __('Teardown end date'), $teardown_end_date),
                    form_date('event_end_date', __('Event end date'), $event_end_date)
                ])
            ]),
            div('row', [
                div('col-md-6', [
                    form_submit('submit', __('Save'))
                ])
            ])
        ])
    ]);
}
