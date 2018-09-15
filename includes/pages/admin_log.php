<?php

/**
 * @return string
 */
function admin_log_title()
{
    return __('Log');
}

/**
 * @return string
 */
function admin_log()
{
    $filter = '';
    if (request()->has('keyword')) {
        $filter = strip_request_item('keyword');
    }
    $log_entries = LogEntries_filter($filter);

    foreach ($log_entries as &$log_entry) {
        $log_entry['date'] = date('d.m.Y H:i', $log_entry['timestamp']);
    }

    return page_with_title(admin_log_title(), [
        msg(),
        form([
            form_text('keyword', __('Search'), $filter),
            form_submit(__('Search'), 'Go')
        ]),
        table([
            'date'    => 'Time',
            'level'   => 'Type',
            'message' => 'Log Entry'
        ], $log_entries)
    ]);
}
