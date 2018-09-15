<?php

/**
 * Shows basic event infos and countdowns.
 *
 * @param array $event_config The event configuration
 * @return string
 */
function EventConfig_countdown_page($event_config)
{
    if (empty($event_config)) {
        return div('col-md-12 text-center', [
            heading(sprintf(__('Welcome to the %s!'), '<span class="icon-icon_angel"></span> ENGELSYSTEM'), 2)
        ]);
    }

    $elements = [];

    if (!is_null($event_config['event_name'])) {
        $elements[] = div('col-sm-12 text-center', [
            heading(sprintf(
                __('Welcome to the %s!'),
                $event_config['event_name'] . ' <span class="icon-icon_angel"></span> ENGELSYSTEM'
            ), 2)
        ]);
    }

    if (!is_null($event_config['buildup_start_date']) && time() < $event_config['buildup_start_date']) {
        $elements[] = div('col-sm-3 text-center hidden-xs', [
            heading(__('Buildup starts'), 4),
            '<span class="moment-countdown text-big" data-timestamp="' . $event_config['buildup_start_date'] . '">%c</span>',
            '<small>' . date(__('Y-m-d'), $event_config['buildup_start_date']) . '</small>'
        ]);
    }

    if (!is_null($event_config['event_start_date']) && time() < $event_config['event_start_date']) {
        $elements[] = div('col-sm-3 text-center hidden-xs', [
            heading(__('Event starts'), 4),
            '<span class="moment-countdown text-big" data-timestamp="' . $event_config['event_start_date'] . '">%c</span>',
            '<small>' . date(__('Y-m-d'), $event_config['event_start_date']) . '</small>'
        ]);
    }

    if (!is_null($event_config['event_end_date']) && time() < $event_config['event_end_date']) {
        $elements[] = div('col-sm-3 text-center hidden-xs', [
            heading(__('Event ends'), 4),
            '<span class="moment-countdown text-big" data-timestamp="' . $event_config['event_end_date'] . '">%c</span>',
            '<small>' . date(__('Y-m-d'), $event_config['event_end_date']) . '</small>'
        ]);
    }

    if (!is_null($event_config['teardown_end_date']) && time() < $event_config['teardown_end_date']) {
        $elements[] = div('col-sm-3 text-center hidden-xs', [
            heading(__('Teardown ends'), 4),
            '<span class="moment-countdown text-big" data-timestamp="' . $event_config['teardown_end_date'] . '">%c</span>',
            '<small>' . date(__('Y-m-d'), $event_config['teardown_end_date']) . '</small>'
        ]);
    }

    return join('', $elements);
}

/**
 * Converts event name and start+end date into a line of text.
 *
 * @param array $event_config
 * @return string
 */
function EventConfig_info($event_config)
{
    if (empty($event_config)) {
        return '';
    }

    // Event name, start+end date are set
    if (
        !is_null($event_config['event_name'])
        && !is_null($event_config['event_start_date'])
        && !is_null($event_config['event_end_date'])
    ) {
        return sprintf(
            __('%s, from %s to %s'),
            $event_config['event_name'],
            date(__('Y-m-d'), $event_config['event_start_date']),
            date(__('Y-m-d'), $event_config['event_end_date'])
        );
    }

    // Event name, start date are set
    if (!is_null($event_config['event_name']) && !is_null($event_config['event_start_date'])) {
        return sprintf(
            __('%s, starting %s'), $event_config['event_name'],
            date(__('Y-m-d'), $event_config['event_start_date'])
        );
    }

    // Event start+end date are set
    if (!is_null($event_config['event_start_date']) && !is_null($event_config['event_end_date'])) {
        return sprintf(
            __('Event from %s to %s'),
            date(__('Y-m-d'), $event_config['event_start_date']),
            date(__('Y-m-d'), $event_config['event_end_date'])
        );
    }

    // Only event name is set
    if (!is_null($event_config['event_name'])) {
        return sprintf($event_config['event_name']);
    }

    return '';
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
