<?php

/**
 * Render edit page for event config.
 *
 * @param string $event_name         The event name
 * @param string $event_welcome_msg  The welcome message
 * @param string $buildup_start_date Date (Y-M-D H:i)
 * @param string $event_start_date   Date (Y-M-D H:i)
 * @param string $event_end_date     Date (Y-M-D H:i)
 * @param string $teardown_end_date  Date (Y-M-D H:i)
 *
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
                    form_datetime('buildup_start_date', __('Buildup date'), $buildup_start_date),
                    form_datetime('event_start_date', __('Event start date'), $event_start_date),
                    form_datetime('event_end_date', __('Event end date'), $event_end_date),
                    form_datetime('teardown_end_date', __('Teardown end date'), $teardown_end_date),
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
